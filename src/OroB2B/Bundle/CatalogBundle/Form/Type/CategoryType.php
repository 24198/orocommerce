<?php

namespace OroB2B\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use OroB2B\Bundle\FallbackBundle\Form\Type\LocalizedFallbackValueCollectionType;

class CategoryType extends AbstractType
{
    const NAME = 'orob2b_catalog_category';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'parentCategory',
            'oro_entity_identifier',
            ['class' => 'OroB2B\Bundle\CatalogBundle\Entity\Category', 'multiple' => false]
        )->add(
            'titles',
            LocalizedFallbackValueCollectionType::NAME,
            [
                'label' => 'orob2b.catalog.category.titles.label',
                'required' => false,
                'options' => ['constraints' => [new NotBlank()]],
            ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroB2B\Bundle\CatalogBundle\Entity\Category',
            'intention' => 'category',
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
