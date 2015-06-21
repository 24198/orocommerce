<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroB2B\Bundle\ProductBundle\Form\Type\ProductVisibilityType;

class ProductVisibilityTypeTest extends FormIntegrationTestCase
{
    /**
     * @var ProductVisibilityType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formType = new ProductVisibilityType();
    }

    /**
     * @dataProvider submitProvider
     *
     * @param bool|null $expectedValue
     */
    public function testSubmit($expectedValue)
    {
        $form = $this->factory->create($this->formType);

        $this->assertNull($form->getData());
        $form->submit($expectedValue);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedValue, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        return [
            'true' => [
                'expectedValue' => true,
            ],
            'false' => [
                'expectedValue' => false,
            ],
            'empty' => [
                'expectedValue' => null,
            ]
        ];
    }

    /**
     * Test getParent
     */
    public function testGetParent()
    {
        $this->assertEquals('choice', $this->formType->getParent());
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertEquals(ProductVisibilityType::NAME, $this->formType->getName());
    }
}
