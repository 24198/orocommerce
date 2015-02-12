<?php

namespace OroB2B\Bundle\AttributeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateAttributeType extends AbstractType
{
    const NAME = 'orob2b_attribute_create';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'text', ['label' => 'orob2b.attribute.code.label'])
            ->add('type', AttributeTypeType::NAME, ['label' => 'orob2b.attribute.type.label'])
            ->add('localized', 'checkbox', ['label' => 'orob2b.attribute.localized.label', 'required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroB2B\Bundle\AttributeBundle\Entity\Attribute',
            'validation_groups' => ['Create']
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
