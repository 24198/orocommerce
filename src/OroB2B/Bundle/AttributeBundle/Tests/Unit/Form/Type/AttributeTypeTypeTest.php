<?php

namespace OroB2B\Bundle\AttributeBundle\Tests\Unit\Form\Type;

use OroB2B\Bundle\AttributeBundle\AttributeType\Boolean;
use OroB2B\Bundle\AttributeBundle\AttributeType\Float;
use OroB2B\Bundle\AttributeBundle\AttributeType\Integer;
use OroB2B\Bundle\AttributeBundle\AttributeType\String;
use OroB2B\Bundle\AttributeBundle\AttributeType\Text;
use OroB2B\Bundle\AttributeBundle\AttributeType\Date;
use OroB2B\Bundle\AttributeBundle\AttributeType\DateTime;
use OroB2B\Bundle\AttributeBundle\Form\Type\AttributeTypeType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class AttributeTypeTypeTest extends FormIntegrationTestCase
{
    /**
     * @var AttributeTypeType
     */
    protected $formType;

    /**
     * @var array
     */
    protected $types = [
        'integer' => 'orob2b.attribute.attribute_type.integer',
        'float' => 'orob2b.attribute.attribute_type.float',
        'string' => 'orob2b.attribute.attribute_type.string',
        'boolean' => 'orob2b.attribute.attribute_type.boolean',
        'text' => 'orob2b.attribute.attribute_type.text',
        'date' => 'orob2b.attribute.attribute_type.date',
        'datetime' => 'orob2b.attribute.attribute_type.datetime'
    ];

    protected function setUp()
    {
        parent::setUp();

        $registry = $this->getMock('OroB2B\Bundle\AttributeBundle\AttributeType\AttributeTypeRegistry');
        $registry->expects($this->any())
            ->method('getTypes')
            ->will($this->returnValue([
                'integer' => new Integer(),
                'float' => new Float(),
                'string' => new String(),
                'boolean' => new Boolean(),
                'text' => new Text(),
                'date' => new Date(),
                'datetime' => new DateTime()

            ]));

        $this->formType = new AttributeTypeType($registry);
    }

    /**
     * @param array $inputOptions
     * @param array $expectedOptions
     * @param mixed $submittedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $inputOptions, array $expectedOptions, $submittedData)
    {
        $form = $this->factory->create($this->formType, null, $inputOptions);

        $formConfig = $form->getConfig();
        foreach ($expectedOptions as $key => $value) {
            $this->assertTrue($formConfig->hasOption($key));
            $this->assertEquals($value, $formConfig->getOption($key));
        }

        $this->assertNull($form->getData());
        $form->submit($submittedData);
        $this->assertEquals($submittedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return [
            'submit integer' => [
                'inputOptions' => [],
                'expectedOptions' => [
                    'required' => true,
                    'empty_value' => 'orob2b.attribute.form.attribute_type.empty',
                    'choices' => $this->types
                ],
                'submittedData' => Integer::NAME,
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(AttributeTypeType::NAME, $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('choice', $this->formType->getParent());
    }
}
