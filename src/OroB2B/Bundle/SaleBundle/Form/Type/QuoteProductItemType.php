<?php

namespace OroB2B\Bundle\SaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;

use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;
use OroB2B\Bundle\SaleBundle\Entity\QuoteProductItem;

class QuoteProductItemType extends AbstractType
{
    const NAME = 'orob2b_sale_quote_product_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', 'integer', [
                'required'  => true,
                'label'     => 'orob2b.sale.quoteproductitem.quantity.label'
            ])
            ->add('price', PriceType::NAME, [
                'required'  => true,
                'label'     => 'orob2b.sale.quoteproductitem.price.label'
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => 'OroB2B\Bundle\SaleBundle\Entity\QuoteProductItem',
            'intention'     => 'sale_quote_product_item',
            'extra_fields_message'  => 'This form should not contain extra fields: "{{ extra_fields }}"'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /* @var $quoteProductItem QuoteProductItem */
        $quoteProductItem = $event->getData();
        $form = $event->getForm();
        $choices = null;

        $productUnitOptons = [
            'compact'   => false,
            'required'  => true,
            'label'     => 'orob2b.product.productunit.entity_label',
        ];

        if ($quoteProductItem && null !== $quoteProductItem->getId()) {
            $product = $quoteProductItem->getQuoteProduct()->getProduct();
            if ($product) {
                $choices = [];
                foreach ($product->getUnitPrecisions() as $unitPrecision) {
                    $choices[] = $unitPrecision->getUnit();
                }
            }
            $productUnit = $quoteProductItem->getProductUnit();
            if (!$productUnit || ($product && !in_array($productUnit->getCode(), $choices))) {
                // ProductUnit was removed
                $productUnitOptons['empty_value'] = $quoteProductItem->getProductUnitCode() . ' - removed';
            }
        }

        $productUnitOptons['choices'] = $choices;

        $form->add(
            'productUnit',
            ProductUnitSelectionType::NAME,
            $productUnitOptons
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $event->getForm()->add(
            'productUnit',
            ProductUnitSelectionType::NAME,
            [
                'compact'   => false,
                'label'     => 'orob2b.product.productunit.entity_label',
            ]
        );
    }
}
