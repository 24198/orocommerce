<?php

namespace Oro\Bundle\WebCatalogBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\RedirectBundle\Form\Type\LocalizedSlugType;
use Oro\Bundle\ValidationBundle\Validator\Constraints\UrlSafe;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeCollectionType;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentNodeType extends AbstractType
{
    const NAME = 'oro_web_catalog_content_node';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'parentNode',
                EntityIdentifierType::NAME,
                ['class' => ContentNode::class, 'multiple' => false]
            )
            ->add(
                'titles',
                LocalizedFallbackValueCollectionType::NAME,
                [
                    'label'    => 'oro.webcatalog.contentnode.titles.label',
                ]
            )
            ->add(
                'scopes',
                ScopeCollectionType::NAME,
                [
                    'mapped' => false,
                    'entry_options' => [
                        'scope_type' => 'web_content'
                    ]
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof ContentNode && $data->getParentNode() instanceof ContentNode) {
            $event->getForm()->add(
                'slugPrototypes',
                LocalizedSlugType::NAME,
                [
                    'label'    => 'oro.webcatalog.contentnode.slug_prototypes.label',
                    'required' => true,
                    'options'  => ['constraints' => [new NotBlank(), new UrlSafe()]],
                    'target_field_name' => 'titles',
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ContentNode::class,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
