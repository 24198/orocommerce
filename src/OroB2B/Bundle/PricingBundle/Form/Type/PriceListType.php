<?php

namespace OroB2B\Bundle\PricingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;

use OroB2B\Bundle\PricingBundle\Entity\PriceList;

class PriceListType extends AbstractType
{
    const NAME = 'orob2b_pricing_price_list';
    const SCHEDULES_FIELD = 'schedules';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PriceList $priceList */
        $priceList = $builder->getData();

        $builder
            ->add('name', 'text', ['required' => true, 'label' => 'orob2b.pricing.pricelist.name.label'])
            ->add(
                self::SCHEDULES_FIELD,
                CollectionType::NAME,
                [
                    'type' => PriceListScheduleType::NAME,
                    'by_reference' => false,
                    'required' => false,
                ]
            )
            ->add(
                'currencies',
                CurrencySelectionType::NAME,
                [
                    'multiple' => true,
                    'required' => true,
                    'label' => 'orob2b.pricing.pricelist.currencies.label',
                    'additional_currencies' => $priceList ? $priceList->getCurrencies() : [],
                ]
            )
            ->add(
                'active',
                'checkbox',
                [
                    'label' => 'orob2b.pricing.pricelist.active.label'
                ]
            )
            ->add(
                'productAssignmentRule',
                'textarea',
                [
                    'label' => 'orob2b.pricing.pricelist.product_assignment_rule.label'
                ]
            )
            ->add(
                'priceRules',
                CollectionType::NAME,
                [
                    'type' => PriceRuleType::NAME,
                    'label' => false
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => PriceList::class
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
