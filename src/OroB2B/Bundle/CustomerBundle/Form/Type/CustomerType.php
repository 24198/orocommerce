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
                CustomerGroupSelectType::NAME,
                [
                    'label' => 'orob2b.customer.group.label',
                    'required' => false
                ]
            )
            ->add(
                'parent',
                ParentCustomerSelectType::NAME,
                [
                    'label' => 'orob2b.customer.parent.label',
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
