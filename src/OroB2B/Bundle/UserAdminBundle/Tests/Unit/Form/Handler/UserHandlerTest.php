<?php

namespace OroB2B\Bundle\UserAdminBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;

use Oro\Component\Testing\Unit\FormHandlerTestCase;

use OroB2B\Bundle\UserAdminBundle\Entity\User;
use OroB2B\Bundle\UserAdminBundle\Form\Handler\UserHandler;

class UserHandlerTest extends FormHandlerTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\OroB2B\Bundle\UserAdminBundle\Mailer\Processor */
    protected $processor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\FOS\UserBundle\Util\TokenGenerator */
    protected $passwordGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $passwordGenerateForm;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $sendEmailForm;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->entity = new User();
        $this->processor = $this->getMockBuilder('OroB2B\Bundle\UserAdminBundle\Mailer\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordGenerator = $this->getMockBuilder('FOS\UserBundle\Util\TokenGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordGenerateForm = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sendEmailForm = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new UserHandler(
            $this->form,
            $this->request,
            $this->manager,
            $this->processor,
            $this->passwordGenerator
        );
    }

    public function testProcessUnsupportedRequest()
    {
        $this->request->setMethod('GET');

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @inheritdoc
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest($method, $isValid, $isProcessed)
    {
        if ($isValid) {
            $this->form->expects($this->at(2))
                ->method('get')
                ->with('passwordGenerate')
                ->will($this->returnValue($this->passwordGenerateForm));

            $this->form->expects($this->at(3))
                ->method('get')
                ->with('sendEmail')
                ->will($this->returnValue($this->sendEmailForm));

            $this->passwordGenerateForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(false));

            $this->sendEmailForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(false));
        }

        $this->form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($isValid));

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertEquals($isProcessed, $this->handler->process($this->entity));
    }

    /**
     * @inheritdoc
     */
    public function testProcessValidData()
    {
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->at(2))
            ->method('get')
            ->with('passwordGenerate')
            ->will($this->returnValue($this->passwordGenerateForm));

        $this->form->expects($this->at(3))
            ->method('get')
            ->with('sendEmail')
            ->will($this->returnValue($this->sendEmailForm));

        $this->passwordGenerateForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(true));

        $this->sendEmailForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(true));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity));
    }
}
