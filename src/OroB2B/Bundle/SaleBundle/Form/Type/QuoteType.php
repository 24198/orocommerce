<?php

namespace OroB2B\Bundle\SaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;

class QuoteType extends AbstractType
{
    const NAME = 'orob2b_sale_quote';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('qid', 'hidden')
            ->add('owner', null, [
            //->add('owner', 'entity', [
            //->add('owner', 'oro_entity_create_or_select', [
            //->add('owner', 'oro_entity_create_or_select_inline', [
                //'class'     => 'OroUserBundle:User',
                'label'     => 'orob2b.sale.quote.owner.label',
                'required'  => true,
            ])
            ->add('validUntil', OroDateTimeType::NAME, [
                'label'     => 'orob2b.sale.quote.valid_until.label',
                'required'  => false,
            ])
            ->add(
                'quoteProducts',
                QuoteProductCollectionType::NAME,
                [
                    'label'     => 'orob2b.sale.quote.quoteproduct.entity_plural_label',
                    'add_label' => 'orob2b.sale.quote.quoteproduct.add_label',
                    'required'  => false,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroB2B\Bundle\SaleBundle\Entity\Quote',
            'intention' => 'sale_quote',
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
