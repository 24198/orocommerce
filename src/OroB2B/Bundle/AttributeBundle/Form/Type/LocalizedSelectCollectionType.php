<?php

namespace OroB2B\Bundle\AttributeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LocalizedSelectCollectionType extends AbstractType
{
    const NAME = 'orob2b_localized_select_collection';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'options' => [
                    'is_default_type' => 'hidden',
                    'value_type' => HiddenFallbackValueType::NAME,
                    'required' => false,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'orob2b_options_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
