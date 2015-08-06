<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\CurrencyBundle\Model\Price;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;

use OroB2B\Bundle\PricingBundle\Tests\Unit\Form\Type\Stub\ProductSelectTypeStub;
use OroB2B\Bundle\PricingBundle\Tests\Unit\Form\Type\Stub\CurrencySelectionTypeStub;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Formatter\ProductUnitLabelFormatter;

use OroB2B\Bundle\SaleBundle\Entity\QuoteProduct;

use OroB2B\Bundle\SaleBundle\Form\Type\QuoteProductType;
use OroB2B\Bundle\SaleBundle\Form\Type\QuoteProductOfferCollectionType;
use OroB2B\Bundle\SaleBundle\Form\Type\QuoteProductRequestCollectionType;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class QuoteProductTypeTest extends AbstractTest
{
    /**
     * @var QuoteProductType
     */
    protected $formType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /* @var $productUnitLabelFormatter \PHPUnit_Framework_MockObject_MockObject|ProductUnitLabelFormatter */
        $productUnitLabelFormatter = $this->getMockBuilder(
            'OroB2B\Bundle\ProductBundle\Formatter\ProductUnitLabelFormatter'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $productUnitLabelFormatter->expects($this->any())
            ->method('format')
            ->will($this->returnCallback(function ($unitCode) {
                return $unitCode . '-formatted';
            }))
        ;

        parent::setUp();

        $this->formType = new QuoteProductType(
            $this->translator,
            $productUnitLabelFormatter,
            $this->quoteProductFormatter
        );
        $this->formType->setDataClass('OroB2B\Bundle\SaleBundle\Entity\QuoteProduct');
    }

    public function testSetDefaultOptions()
    {
        /* @var $resolver \PHPUnit_Framework_MockObject_MockObject|OptionsResolverInterface */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class'    => 'OroB2B\Bundle\SaleBundle\Entity\QuoteProduct',
                'intention'     => 'sale_quote_product',
                'extra_fields_message'  => 'This form should not contain extra fields: "{{ extra_fields }}"'
            ])
        ;

        $this->formType->setDefaultOptions($resolver);
    }

    /**
     * @param array $inputData
     * @param array $expectedData
     *
     * @dataProvider finishViewProvider
     */
    public function testFinishView(array $inputData, array $expectedData)
    {
        $view = new FormView();

        $view->vars = $inputData;

        /* @var $form \PHPUnit_Framework_MockObject_MockObject|FormInterface */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $this->formType->finishView($view, $form, []);

        $this->assertEquals($expectedData, $view->vars);
    }

    /**
     * @param QuoteProduct $inputData
     * @param array $expectedData
     *
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData(QuoteProduct $inputData = null, array $expectedData = [])
    {
        $this->translator->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id, array $params) {
                return $id . ':' .$params['{title}'];
            }))
        ;

        $form = $this->factory->create($this->formType);

        $this->formType->preSetData(new FormEvent($form, $inputData));

        foreach ($expectedData as $field => $fieldOptions) {
            $options = $form->get($field)->getConfig()->getOptions();

            foreach ($fieldOptions as $key => $value) {
                $this->assertEquals($value, $options[$key], $key);
            }
        }
    }

    /**
     * @return array
     */
    public function finishViewProvider()
    {
        return [
            'empty quote product' => [
                'input'     => [
                    'value' => null,
                ],
                'expected'  => [
                    'value' => null,
                    'componentOptions' => [
                        'units' => [],
                        'typeOffer'         => QuoteProduct::TYPE_OFFER,
                        'typeReplacement'   => QuoteProduct::TYPE_NOT_AVAILABLE,
                    ],
                ],
            ],
            'empty product and replacement' => [
                'input'     => [
                    'value' => new QuoteProduct(),
                ],
                'expected'  => [
                    'value' => new QuoteProduct(),
                    'componentOptions' => [
                        'units' => [],
                        'typeOffer'         => QuoteProduct::TYPE_OFFER,
                        'typeReplacement'   => QuoteProduct::TYPE_NOT_AVAILABLE,
                    ],
                ],
            ],
            'existing product and replacement' => [
                'input'     => [
                    'value' => (new QuoteProduct())
                        ->setProduct($this->createProduct(1, ['unit1', 'unit2']))
                        ->setProductReplacement($this->createProduct(2, ['unit2', 'unit3'])),
                ],
                'expected'  => [
                    'value' => (new QuoteProduct())
                        ->setProduct($this->createProduct(1, ['unit1', 'unit2']))
                        ->setProductReplacement($this->createProduct(2, ['unit2', 'unit3'])),
                    'componentOptions' => [
                        'units' => [
                            1 => [
                                'unit1' => 'unit1-formatted',
                                'unit2' => 'unit2-formatted',
                            ],
                            2 => [
                                'unit2' => 'unit2-formatted',
                                'unit3' => 'unit3-formatted',
                            ],
                        ],
                        'typeOffer'         => QuoteProduct::TYPE_OFFER,
                        'typeReplacement'   => QuoteProduct::TYPE_NOT_AVAILABLE,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @param array $units
     * @return \PHPUnit_Framework_MockObject_MockObject|Product
     */
    protected function createProduct($id, array $units = [])
    {
        $product = $this->getMockEntity(
            'OroB2B\Bundle\ProductBundle\Entity\Product',
            [
                'getId' => $id,
                'getAvailableUnitCodes' => $units,
            ]
        );

        return $product;
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        $quoteProductOffer = $this->getQuoteProductOffer(2, 10, 'kg', self::QPO_PRICE_TYPE1, Price::create(20, 'USD'));

        return [
            'empty form' => [
                'isValid'       => false,
                'submittedData' => [
                ],
                'expectedData'  => $this->getQuoteProduct(2)->setProduct(null),
                'inputData'     => $this->getQuoteProduct(2)->setProduct(null),
            ],
            'empty quote' => [
                'isValid'       => false,
                'submittedData' => [
                    'product'   => 2,
                    'type'      => self::QP_TYPE1,
                    'comment'   => 'comment1',
                    'commentAccount' => 'comment2',
                    'quoteProductOffers' => [
                        [
                            'quantity'      => 10,
                            'productUnit'   => 'kg',
                            'priceType'     => self::QPO_PRICE_TYPE1,
                            'price'         => [
                                'value'     => 20,
                                'currency'  => 'USD',
                            ],
                        ],
                    ],
                ],
                'expectedData' => $this->getQuoteProduct(
                    2,
                    self::QP_TYPE1,
                    'comment1',
                    'comment2',
                    [],
                    [
                        clone $quoteProductOffer,
                    ]
                )->setQuote(null),
                'inputData' => $this->getQuoteProduct(2)->setQuote(null)->setProduct(null),
            ],
            'empty product' => [
                'isValid'       => false,
                'submittedData' => [
                    'type'      => self::QP_TYPE1,
                    'comment'   => 'comment1',
                    'commentAccount' => 'comment2',
                    'quoteProductOffers' => [
                        [
                            'quantity'      => 10,
                            'productUnit'   => 'kg',
                            'priceType'     => self::QPO_PRICE_TYPE1,
                            'price'         => [
                                'value'     => 20,
                                'currency'  => 'USD',
                            ],
                        ],
                    ],
                ],
                'expectedData' => $this->getQuoteProduct(
                    2,
                    self::QP_TYPE1,
                    'comment1',
                    'comment2',
                    [],
                    [
                        clone $quoteProductOffer,
                    ]
                )->setProduct(null),
                'inputData' => $this->getQuoteProduct(2)->setProduct(null),
            ],
            'empty type' => [
                'isValid'       => false,
                'submittedData' => [
                    'product'   => 2,
                    'comment'   => 'comment1',
                    'commentAccount' => 'comment2',
                    'quoteProductOffers' => [
                        [
                            'quantity'      => 10,
                            'productUnit'   => 'kg',
                            'priceType'     => self::QPO_PRICE_TYPE1,
                            'price'         => [
                                'value'     => 20,
                                'currency'  => 'USD',
                            ],
                        ],
                    ],
                ],
                'expectedData' => $this->getQuoteProduct(
                    2,
                    null,
                    'comment1',
                    'comment2',
                    [],
                    [
                        clone $quoteProductOffer,
                    ]
                ),
                'inputData' => $this->getQuoteProduct(2)->setProduct(null),
            ],
            'empty offers' => [
                'isValid'       => false,
                'submittedData' => [
                    'product'   => 2,
                    'comment'   => 'comment1',
                    'commentAccount' => 'comment2',
                ],
                'expectedData'  => $this->getQuoteProduct(2, null, 'comment1', 'comment2', [], []),
                'inputData'     => $this->getQuoteProduct(2)->setProduct(null),
            ],
            'valid data' => [
                'isValid'       => true,
                'submittedData' => [
                    'product'   => 2,
                    'type'      => self::QP_TYPE1,
                    'comment'   => 'comment1',
                    'commentAccount' => 'comment2',
                    'quoteProductOffers' => [
                        [
                            'quantity'      => 10,
                            'productUnit'   => 'kg',
                            'priceType'     => self::QPO_PRICE_TYPE1,
                            'price'         => [
                                'value'     => 20,
                                'currency'  => 'USD',
                            ],
                        ],
                    ],
                ],
                'expectedData' => $this->getQuoteProduct(
                    2,
                    self::QP_TYPE1,
                    'comment1',
                    'comment2',
                    [],
                    [
                        clone $quoteProductOffer,
                    ]
                ),
                'inputData' => $this->getQuoteProduct(2)->setProduct(null),
            ],
        ];
    }

    /**
     * @return array
     */
    public function preSetDataProvider()
    {
        return [
            'empty item' => [
                'inputData'     => null,
                'expectedData'  => [
                    'product' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => true,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.product.entity_label',
                    ],
                    'productReplacement' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => false,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.sale.quoteproduct.product_replacement.label',
                    ],
                ],
            ],
            'deleted product' => [
                'inputData'     => $this->createQuoteProduct(1, null, 'sku', null, ''),
                'expectedData'  => [
                    'product' => [
                        'configs'   => [
                            'placeholder'   => 'orob2b.sale.quoteproduct.product.removed:sku',
                        ],
                        'required'          => true,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.product.entity_label',
                    ],
                    'productReplacement' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => false,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.sale.quoteproduct.product_replacement.label',
                    ],
                ],
            ],
            'deleted product replacement' => [
                'inputData'     => $this->createQuoteProduct(
                    1,
                    new Product(),
                    'sku',
                    null,
                    'sku2',
                    QuoteProduct::TYPE_NOT_AVAILABLE
                ),
                'expectedData'  => [
                    'product' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => true,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.product.entity_label',
                    ],
                    'productReplacement' => [
                        'configs'   => [
                            'placeholder'   => 'orob2b.sale.quoteproduct.product_replacement.removed:sku2',
                        ],
                        'required'          => false,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.sale.quoteproduct.product_replacement.label',
                    ],
                ],
            ],
            'existing product and replacement' => [
                'inputData'     => $this->createQuoteProduct(
                    1,
                    new Product(),
                    'sku',
                    new Product(),
                    'sku2',
                    QuoteProduct::TYPE_NOT_AVAILABLE
                ),
                'expectedData'  => [
                    'product' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => true,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.product.entity_label',
                    ],
                    'productReplacement' => [
                        'configs'   => [
                            'placeholder'   => null,
                        ],
                        'required'          => false,
                        'create_enabled'    => false,
                        'label'             => 'orob2b.sale.quoteproduct.product_replacement.label',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @param Product $product
     * @param string $productSku
     * @param Product $replacement
     * @param string $replacementSku
     * @param int $type
     * @return \PHPUnit_Framework_MockObject_MockObject|QuoteProduct
     */
    protected function createQuoteProduct(
        $id,
        Product $product = null,
        $productSku = null,
        Product $replacement = null,
        $replacementSku = null,
        $type = QuoteProduct::TYPE_OFFER
    ) {
        /* @var $quoteProduct \PHPUnit_Framework_MockObject_MockObject|QuoteProduct */
        $quoteProduct = $this->getMock('OroB2B\Bundle\SaleBundle\Entity\QuoteProduct');
        $quoteProduct
            ->expects($this->any())
            ->method('getId')
            ->willReturn($id)
        ;
        $quoteProduct
            ->expects($this->any())
            ->method('isTypeNotAvailable')
            ->willReturn($type === QuoteProduct::TYPE_NOT_AVAILABLE)
        ;
        $quoteProduct
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product)
        ;
        $quoteProduct
            ->expects($this->any())
            ->method('getProductSku')
            ->willReturn($productSku)
        ;
        $quoteProduct
            ->expects($this->any())
            ->method('getProductReplacement')
            ->willReturn($replacement)
        ;
        $quoteProduct
            ->expects($this->any())
            ->method('getProductReplacementSku')
            ->willReturn($replacementSku)
        ;

        return $quoteProduct;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $priceType                  = $this->preparePriceType();
        $entityType                 = $this->prepareProductEntityType();
        $productSelectType          = new ProductSelectTypeStub();
        $currencySelectionType      = new CurrencySelectionTypeStub();
        $productUnitSelectionType   = $this->prepareProductUnitSelectionType();

        $quoteProductOfferType      = $this->prepareQuoteProductOfferType($this->translator);
        $quoteProductRequestType    = $this->prepareQuoteProductRequestType($this->translator);

        return [
            new PreloadedExtension(
                [
                    CollectionType::NAME                        => new CollectionType(),
                    QuoteProductOfferCollectionType::NAME       => new QuoteProductOfferCollectionType(),
                    QuoteProductRequestCollectionType::NAME     => new QuoteProductRequestCollectionType(),
                    $priceType->getName()                       => $priceType,
                    $entityType->getName()                      => $entityType,
                    $productSelectType->getName()               => $productSelectType,
                    $currencySelectionType->getName()           => $currencySelectionType,
                    $quoteProductOfferType->getName()           => $quoteProductOfferType,
                    $quoteProductRequestType->getName()         => $quoteProductRequestType,
                    $productUnitSelectionType->getName()        => $productUnitSelectionType,
                ],
                []
            ),
            $this->getValidatorExtension(true),
        ];
    }
}
