<?php

namespace OroB2B\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use OroB2B\Bundle\OrderBundle\Entity\Order;
use OroB2B\Bundle\OrderBundle\Model\OrderAddressManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderAddressType extends AbstractType
{
    const MANUAL_EDIT_ACTION = 'orob2b_order_address_%s_allow_manual';

    const NAME = 'orob2b_order_address_type';

    /** @var string */
    protected $dataClass;

    /**
     * @var AddressFormatter
     */
    protected $addressFormatter;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var OrderAddressManager
     */
    protected $orderAddressManager;

    /**
     * @param AddressFormatter $addressFormatter
     * @param SecurityFacade $securityFacade
     * @param OrderAddressManager $orderAddressManager
     */
    public function __construct(
        AddressFormatter $addressFormatter,
        SecurityFacade $securityFacade,
        OrderAddressManager $orderAddressManager
    ) {
        $this->addressFormatter = $addressFormatter;
        $this->securityFacade = $securityFacade;
        $this->orderAddressManager = $orderAddressManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = $options['addressType'];

        $builder->add(
            'accountAddress',
            'genemu_jqueryselect2_choice',
            [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'choices' => $this->getAddresses($options['order'], $type),
            ]
        );

        if (!$this->isManualEditGranted($type)) {
            $builder->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $identifier = $form->get('accountAddress')->getData();
                    if ($identifier) {
                        $address = $this->orderAddressManager->getEntityByIdentifier($identifier);
                        if ($address) {
                            $event->setData(
                                $this->orderAddressManager->updateFromAbstract($address, $event->getData())
                            );
                        }
                    }
                }
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars['read_only'] = !$this->isManualEditGranted($options['addressType']);
        }

        $view->offsetGet('accountAddress')->vars['read_only'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['order', 'addressType'])
            ->setDefaults(['data_class' => $this->dataClass])
            ->setAllowedValues('addressType', [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING])
            ->setAllowedTypes('order', 'OroB2B\Bundle\OrderBundle\Entity\Order');
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_address';
    }


    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param Order $order
     * @param string $type
     *
     * @return array
     */
    protected function getAddresses(Order $order, $type)
    {
        $addresses = $this->orderAddressManager->getGroupedAddresses($order, $type);

        array_walk_recursive(
            $addresses,
            function (&$item) {
                if ($item instanceof AbstractAddress) {
                    $item = $this->addressFormatter->format($item, null, ', ');
                }

                return $item;
            }
        );

        return $addresses;
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function isManualEditGranted($type)
    {
        return $this->securityFacade->isGranted(sprintf(self::MANUAL_EDIT_ACTION, $type));
    }
}
