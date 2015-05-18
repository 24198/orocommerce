<?php

namespace OroB2B\Bundle\CustomerAdminBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroB2B\Bundle\CustomerAdminBundle\Form\Type\ParentCustomerSelectType;

class ParentCustomerSelectTypeStub extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ParentCustomerSelectType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'class' => 'OroB2B\Bundle\CustomerAdminBundle\Entity\Customer',
            'property' => 'name'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }
}
