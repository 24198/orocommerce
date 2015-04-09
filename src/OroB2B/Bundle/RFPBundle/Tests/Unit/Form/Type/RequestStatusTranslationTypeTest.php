<?php

namespace OroB2B\Bundle\RFPBundle\Tests\Unit\Form\Type;

use OroB2B\Bundle\RFPBundle\Form\Type\RequestStatusTranslationType;

use Symfony\Component\Form\FormView;

class RequestStatusTranslationTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestStatusTranslationType
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->type = new RequestStatusTranslationType();
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertEquals(RequestStatusTranslationType::NAME, $this->type->getName());
    }

    /**
     * Test getParent
     */
    public function testGetParent()
    {
        $this->assertEquals('a2lix_translations_gedmo', $this->type->getParent());
    }

    /**
     * Test setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $optionsResolver->expects($this->once())
            ->method('setDefaults');

        $this->type->setDefaultOptions($optionsResolver);
    }

    /**
     * Test buildView
     */
    public function testBuildView()
    {
        $formView = new FormView();

        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $labels = [1, 2, 3];

        $this->type->buildView($formView, $form, ['labels' => $labels]);

        $this->assertEquals($formView->vars['labels'], $labels);
    }
}
