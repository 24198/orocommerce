<?php

namespace OroB2B\Bundle\AccountBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber;

use OroB2B\Bundle\AccountBundle\Form\EventListener\FixAccountAddressesDefaultSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountTypedAddressType extends AbstractType
{
    const NAME = 'orob2b_account_typed_address';

    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $addressTypeDataClass;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['single_form'] && $options['all_addresses_property_path']) {
            $builder->addEventSubscriber(
                new FixAddressesPrimarySubscriber($options['all_addresses_property_path'])
            );
            $builder->addEventSubscriber(
                new FixAccountAddressesDefaultSubscriber($options['all_addresses_property_path'])
            );
        }

        $builder
            ->add(
                'types',
                'translatable_entity',
                [
                    'class'    => $this->addressTypeDataClass,
                    'property' => 'label',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true
                ]
            )
            ->add(
                'defaults',
                AccountTypedAddressWithDefaultType::NAME,
                [
                    'class'    => $this->addressTypeDataClass,
                    'required' => false,
                ]
            )
            ->add(
                'primary',
                'checkbox',
                [
                    'required' => false
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'single_form' => true,
                'all_addresses_property_path' => 'owner.addresses'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_address';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @param string $addressTypeDataClass
     */
    public function setAddressTypeDataClass($addressTypeDataClass)
    {
        $this->addressTypeDataClass = $addressTypeDataClass;
    }
}
