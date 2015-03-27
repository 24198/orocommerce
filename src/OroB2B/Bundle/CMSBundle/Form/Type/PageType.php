<?php

namespace OroB2B\Bundle\CMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;

use OroB2B\Bundle\CMSBundle\Entity\Page;
use OroB2B\Bundle\RedirectBundle\Entity\Slug;

class PageType extends AbstractType
{
    const NAME = 'orob2b_cms_page';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'parentPage',
                EntityIdentifierType::NAME,
                [
                    'class' => 'OroB2B\Bundle\CMSBundle\Entity\Page',
                    'multiple' => false
                ]
            )
            ->add(
                'title',
                'text',
                [
                    'label' => 'orob2b.cms.page.title.label',
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'content',
                OroRichTextType::NAME,
                [
                    'label' => 'orob2b.cms.page.content.label',
                    'required' => false,
                    'wysiwyg_options' => [
                        'statusbar' => true,
                        'resize' => true,
                    ]
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Page $page */
            $page = $event->getData();
            $form = $event->getForm();

            if ($page && $page->getId()) {
                $form->add(
                    'slug',
                    SlugType::NAME,
                    [
                        'label' => 'orob2b.redirect.slug.entity_label',
                        'mapped' => false, 'type' => 'update', 'current_slug' => $page->getCurrentSlug()->getSlugUrl()]
                );
            } else {
                $form->add(
                    'slug',
                    SlugType::NAME,
                    [
                        'label' => 'orob2b.redirect.slug.entity_label',
                        'mapped' => false, 'type' => 'create'
                    ]
                );
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $slugData = $event->getForm()->get('slug')->getData();
            /** @var Page $page */
            $page = $event->getData();

            if ($slugData['mode'] == 'new') {
                if (isset($slugData['redirect']) && $slugData['redirect']) {
                    // Leave the old slug for page. And add a new slug as current for page
                    $slug = new Slug();
                    $slug->setUrl($slugData['slug']);
                    $page->setCurrentSlug($slug);
                } else {
                    // Change current slug url
                    $page->setCurrentSlugUrl($slugData['slug']);
                }
            }
        });
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroB2B\Bundle\CMSBundle\Entity\Page',
            'intention' => 'page',
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
