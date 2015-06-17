<?php

namespace OroB2B\Bundle\AttributeBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\PreloadedExtension;

use OroB2B\Bundle\FallbackBundle\Form\Type\FallbackPropertyType;
use OroB2B\Bundle\FallbackBundle\Form\Type\FallbackValueType;
use OroB2B\Bundle\AttributeBundle\Form\Type\HiddenFallbackValueType;
use OroB2B\Bundle\FallbackBundle\Form\Type\LocaleCollectionType;
use OroB2B\Bundle\AttributeBundle\Form\Type\OptionRowType;
use OroB2B\Bundle\FallbackBundle\Model\FallbackType;
use OroB2B\Bundle\AttributeBundle\Tests\Unit\Form\Type\Stub\IntegerType;
use OroB2B\Bundle\AttributeBundle\Tests\Unit\Form\Type\Stub\TextType;
use OroB2B\Bundle\FallbackBundle\Tests\Unit\Form\Type\AbstractLocalizedType;

class OptionRowTypeTest extends AbstractLocalizedType
{
    /**
     * @var OptionRowType
     */
    protected $formType;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $builder = new FormFactoryBuilder();
        $builder->addExtensions($this->getExtensions())
            ->addExtension(new CoreExtension());

        $this->factory = $builder->getFormFactory();

        $this->formType = new OptionRowType();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $localeCollection = new LocaleCollectionType($this->registry);
        $localeCollection->setLocaleClass(self::LOCALE_CLASS);

        return [
            new PreloadedExtension(
                [
                    'text' => new TextType(),
                    'integer' => new IntegerType(),
                    FallbackValueType::NAME => new FallbackValueType(),
                    LocaleCollectionType::NAME => $localeCollection,
                    FallbackPropertyType::NAME => new FallbackPropertyType(),
                    HiddenFallbackValueType::NAME => new HiddenFallbackValueType()
                ],
                []
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $options, $defaultData, $viewData, $submittedData, $expectedData)
    {
        $this->setRegistryExpectations();

        $form = $this->factory->create($this->formType, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        foreach ($viewData as $field => $data) {
            $this->assertEquals($data, $form->get($field)->getViewData());
        }

        $this->assertEquals('checkbox', $form->createView()->vars['is_default_type']);

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider()
    {
        return [
            'option with hidden fallback' => [
                'options' => [
                    'value_type' => 'orob2b_attribute_hidden_fallback'
                ],
                'defaultData' => [
                    OptionRowType::ORDER => 0,
                ],
                'viewData' => [
                    OptionRowType::DEFAULT_VALUE => null,
                    OptionRowType::IS_DEFAULT => null,
                    OptionRowType::ORDER => 0,
                    OptionRowType::MASTER_OPTION_ID => null,
                    OptionRowType::LOCALES => [
                        1 => ['fallback_value' => new FallbackType(FallbackType::SYSTEM)],
                        2 => ['fallback_value' => new FallbackType(FallbackType::PARENT_LOCALE)],
                        3 => ['fallback_value' => new FallbackType(FallbackType::PARENT_LOCALE)],
                    ]
                ],
                'submittedData' => [
                    OptionRowType::DEFAULT_VALUE => 'default value',
                    OptionRowType::ORDER => '10',
                    OptionRowType::MASTER_OPTION_ID => null
                ],
                'expectedData' => [
                    OptionRowType::MASTER_OPTION_ID => null,
                    'order' => '10',
                    'data' => [
                        null => [
                            'value' => 'default value',
                            'is_default' => false,
                        ],
                        1 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        2 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        3 => [
                            'value' => null,
                            'is_default' => false,
                        ]
                    ]
                ],
            ],
            'option without submitted data' => [
                'options' => [],
                'defaultData' => [
                    OptionRowType::ORDER => 0,
                ],
                'viewData' => [],
                'submittedData' => null,
                'expectedData' => [
                    OptionRowType::MASTER_OPTION_ID => null,
                    'order' => null,
                    'data' => [
                        null => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        1 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        2 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        3 => [
                            'value' => null,
                            'is_default' => false,
                        ]
                    ]
                ],
            ],
            'option without data' => [
                'options' => [],
                'defaultData' => [
                    OptionRowType::ORDER => 0,
                ],
                'viewData' => [
                    OptionRowType::DEFAULT_VALUE => null,
                    OptionRowType::IS_DEFAULT => null,
                    OptionRowType::ORDER => 0,
                    OptionRowType::MASTER_OPTION_ID => null,
                    OptionRowType::LOCALES => [
                        1 => new FallbackType(FallbackType::SYSTEM),
                        2 => new FallbackType(FallbackType::PARENT_LOCALE),
                        3 => new FallbackType(FallbackType::PARENT_LOCALE),
                    ]
                ],
                'submittedData' => [
                    OptionRowType::DEFAULT_VALUE => 'default value',
                    OptionRowType::ORDER => '10',
                    OptionRowType::MASTER_OPTION_ID => null
                ],
                'expectedData' => [
                    OptionRowType::MASTER_OPTION_ID => null,
                    'order' => '10',
                    'data' => [
                        null => [
                            'value' => 'default value',
                            'is_default' => false,
                        ],
                        1 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        2 => [
                            'value' => null,
                            'is_default' => false,
                        ],
                        3 => [
                            'value' => null,
                            'is_default' => false,
                        ]
                    ]
                ],
            ],
            'option with data' => [
                'options' => [],
                'defaultData' => [
                    OptionRowType::MASTER_OPTION_ID => 1,
                    'order' => 5,
                    'data' => [
                        null => [
                            'value' => 'default value',
                            'is_default' => true,
                        ],
                        1 => [
                            'value' => new FallbackType(FallbackType::SYSTEM),
                            'is_default' => false,
                        ],
                        2 => [
                            'value' => new FallbackType(FallbackType::PARENT_LOCALE),
                            'is_default' => false,
                        ],
                        3 => [
                            'value' => new FallbackType(FallbackType::PARENT_LOCALE),
                            'is_default' => false,
                        ]
                    ]
                ],
                'viewData' => [
                    OptionRowType::DEFAULT_VALUE => 'default value',
                    OptionRowType::IS_DEFAULT => true,
                    OptionRowType::ORDER => 5,
                    OptionRowType::MASTER_OPTION_ID => 1,
                    OptionRowType::LOCALES => [
                        1 => new FallbackType(FallbackType::SYSTEM),
                        2 => new FallbackType(FallbackType::PARENT_LOCALE),
                        3 => new FallbackType(FallbackType::PARENT_LOCALE),
                    ]
                ],
                'submittedData' => [
                    OptionRowType::DEFAULT_VALUE => 'new default value',
                    OptionRowType::ORDER => '15',
                    OptionRowType::MASTER_OPTION_ID => '1',
                    OptionRowType::LOCALES => [
                        1 => ['value' => '', 'fallback' => FallbackType::SYSTEM],
                        2 => ['value' => 'en_US value', 'fallback' => ''],
                        3 => ['value' => '', 'fallback' => FallbackType::PARENT_LOCALE],
                    ]
                ],
                'expectedData' => [
                    OptionRowType::MASTER_OPTION_ID => 1,
                    'order' => '15',
                    'data' => [
                        null => [
                            'value' => 'new default value',
                            'is_default' => false,
                        ],
                        1 => [
                            'value' => new FallbackType(FallbackType::SYSTEM),
                            'is_default' => false,
                        ],
                        2 => [
                            'value' => 'en_US value',
                            'is_default' => false,
                        ],
                        3 => [
                            'value' => new FallbackType(FallbackType::PARENT_LOCALE),
                            'is_default' => false,
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(OptionRowType::NAME, $this->formType->getName());
    }
}
