<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;

use OroB2B\Bundle\ProductBundle\Form\Type\ProductRowCollectionType;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductRowType;
use OroB2B\Bundle\ProductBundle\Form\Type\QuickAddType;

class QuickAddTypeTest extends FormIntegrationTestCase
{
    /** @var QuickAddType */
    protected $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->formType = new QuickAddType();

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                ProductRowCollectionType::NAME => new ProductRowCollectionType(),
                ProductRowType::NAME => new ProductRowType(),
                CollectionType::NAME => new CollectionType(),
            ], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param mixed $submittedData
     * @param mixed $expectedData
     */
    public function testSubmit($submittedData, $expectedData)
    {
        $form = $this->factory->create($this->formType);
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return [
            'empty' => [
                'submittedData' => [],
                'expectedData' => [
                    QuickAddType::PRODUCTS_FIELD_NAME => [],
                    QuickAddType::COMPONENT_FIELD_NAME => null,
                    QuickAddType::ADDITIONAL_FIELD_NAME => null,
                ],
            ],
            'valid data' => [
                'submittedData' => [
                    QuickAddType::PRODUCTS_FIELD_NAME => [
                        [
                            ProductRowType::PRODUCT_SKU_FIELD_NAME => 'sku',
                            ProductRowType::PRODUCT_QUANTITY_FIELD_NAME => '42',
                        ]
                    ],
                    QuickAddType::COMPONENT_FIELD_NAME => 'component',
                    QuickAddType::ADDITIONAL_FIELD_NAME => 'additional',
                ],
                'expectedData' => [
                    QuickAddType::PRODUCTS_FIELD_NAME => [
                        [
                            ProductRowType::PRODUCT_SKU_FIELD_NAME => 'sku',
                            ProductRowType::PRODUCT_QUANTITY_FIELD_NAME => 42,
                        ]
                    ],
                    QuickAddType::COMPONENT_FIELD_NAME => 'component',
                    QuickAddType::ADDITIONAL_FIELD_NAME => 'additional',
                ],
            ],
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(QuickAddType::NAME, $this->formType->getName());
    }
}
