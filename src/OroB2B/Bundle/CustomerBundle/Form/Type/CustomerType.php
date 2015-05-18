<?php

namespace OroB2B\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerType extends AbstractType
{
    const NAME = 'orob2b_customer_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['label' => 'orob2b.customer.name.label'])
            ->add(
                'group',
                'orob2b_customer_group_select',
                [
                    'label' => 'orob2b.customer.group.label',
                    'required' => false
                ]
            )
            ->add(
                'parent',
                'entity',
                [
                    'label' => 'orob2b.customer.parent.label',
                    'class' => 'OroB2B\Bundle\CustomerBundle\Entity\Customer',
                    'property' => 'name',
                    'required' => false
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
