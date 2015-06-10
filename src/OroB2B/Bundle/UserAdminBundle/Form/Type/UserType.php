<?php

namespace OroB2B\Bundle\UserAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use OroB2B\Bundle\UserAdminBundle\Entity\User;
use OroB2B\Bundle\CustomerBundle\Form\Type\CustomerSelectType;

class UserType extends AbstractType
{
    const NAME = 'orob2b_user_admin_user';

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                'text',
                ['required' => true, 'label' => 'orob2b.useradmin.user.first_name.label']
            )
            ->add('lastName', 'text', ['required' => true, 'label' => 'orob2b.useradmin.user.last_name.label'])
            ->add('email', 'email', ['required' => true, 'label' => 'orob2b.useradmin.user.email.label'])
            ->add(
                'customer',
                CustomerSelectType::NAME,
                ['required' => false, 'label' => 'orob2b.useradmin.user.customer.label']
            )
            ->add(
                'enabled',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'orob2b.useradmin.user.enabled.label',
                    'data' => true
                ]
            )
        ;
        $data = $builder->getData();

        $passwordOptions = [
            'type'            => 'password',
            'required'        => false,
            'first_options'   => ['label' => 'orob2b.useradmin.user.password.label'],
            'second_options'  => ['label' => 'orob2b.useradmin.user.password_confirmation.label'],
            'invalid_message' => $this->translator->trans('orob2b.useradmin.message.password_mismatch')
        ];

        if ($data instanceof User && $data->getId()) {
            $passwordOptions = array_merge($passwordOptions, ['required' => false, 'validation_groups' => false]);
        } else {
            $builder
                ->add(
                    'passwordGenerate',
                    'checkbox',
                    [
                        'required' => false,
                        'label'    => 'orob2b.useradmin.user.password_generate.label',
                        'mapped'   => false
                    ]
                )
                ->add(
                    'sendEmail',
                    'checkbox',
                    [
                        'required' => false,
                        'label'    => 'orob2b.useradmin.user.send_email.label',
                        'mapped'   => false
                    ]
                );

            $passwordOptions = array_merge($passwordOptions, ['required' => true, 'validation_groups' => 'create']);
        }

        $builder->add(
            'plainPassword',
            'repeated',
            $passwordOptions
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'           => 'OroB2B\Bundle\UserAdminBundle\Entity\User',
            'intention'            => 'user',
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
