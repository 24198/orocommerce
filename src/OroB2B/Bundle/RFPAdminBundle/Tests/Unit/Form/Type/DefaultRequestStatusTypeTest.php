<?php

namespace OroB2B\Bundle\RFPAdminBundle\Tests\Unit\Form\Type;

use OroB2B\Bundle\RFPAdminBundle\Form\Type\DefaulRequestStatusType;

class DefaultRequestStatusTypeTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_STATUS_CLASS = 'OroB2B\Bundle\RFPAdminBundle\Entity\RequestStatus';

    /**
     * @var DefaulRequestStatusType
     */
    protected $formType;

    /**
     * @var \OroB2B\Bundle\RFPAdminBundle\Entity\RequestStatus[]
     */
    protected $choices;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->choices = [
            $this->getMock(self::REQUEST_STATUS_CLASS),
            $this->getMock(self::REQUEST_STATUS_CLASS),
        ];

        $repository = $this->getMockBuilder('OroB2B\Bundle\RFPAdminBundle\Entity\Repository\RequestStatusRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('getNotDeletedStatuses')
            ->willReturn($this->choices);

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->any())
            ->method('getRepository')
            ->with(self::REQUEST_STATUS_CLASS)
            ->willReturn($repository);

        $this->formType = new DefaulRequestStatusType($registry);
        $this->formType->setRequestStatusClass(self::REQUEST_STATUS_CLASS);
    }

    /**
     * Test setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->withAnyParameters();

        $this->formType->setDefaultOptions($resolver);
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertEquals(DefaulRequestStatusType::NAME, $this->formType->getName());
    }

    /**
     * Test getParent
     */
    public function testGetParent()
    {
        $this->assertEquals('choice', $this->formType->getParent());
    }
}
