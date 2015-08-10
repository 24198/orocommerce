<?php

namespace OroB2B\Bundle\OrderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroB2B\Bundle\AccountBundle\Form\Type\AccountSelectType;
use OroB2B\Bundle\PaymentBundle\Form\Type\PaymentTermSelectType;
use OroB2B\Bundle\OrderBundle\Entity\Order;
use OroB2B\Bundle\OrderBundle\Provider\OrderAddressSecurityProvider;

class OrderType extends AbstractType
{
    const NAME = 'orob2b_order_type';

    /** @var  string */
    protected $dataClass;

    /** @var OrderAddressSecurityProvider */
    protected $orderAddressSecurityProvider;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param OrderAddressSecurityProvider $orderAddressSecurityProvider
     */
    public function __construct(SecurityFacade $securityFacade, OrderAddressSecurityProvider $orderAddressSecurityProvider)
    {
        $this->securityFacade = $securityFacade;
        $this->orderAddressSecurityProvider = $orderAddressSecurityProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Order $order */
        $order = $options['data'];

        $builder
            ->add('account', AccountSelectType::NAME, ['label' => 'orob2b.order.account.label', 'required' => true])
            // @todo: user selector
            ->add(
                'accountUser',
                'entity',
                [
                    'class' => 'OroB2B\Bundle\AccountBundle\Entity\AccountUser',
                    'label' => 'orob2b.order.account_user.label',
                    'required' => false,
                ]
            )
            ->add('poNumber', 'text', ['required' => false, 'label' => 'orob2b.order.po_number.label'])
            ->add('shipUntil', OroDateType::NAME, ['required' => false, 'label' => 'orob2b.order.ship_until.label']);

        if ($this->orderAddressSecurityProvider->isAddressGranted($order, AddressType::TYPE_BILLING)) {
            $builder
                ->add(
                    'billingAddress',
                    OrderAddressType::NAME,
                    [
                        'label' => 'orob2b.order.billing_address.label',
                        'order' => $options['data'],
                        'required' => false,
                        'addressType' => AddressType::TYPE_BILLING,
                    ]
                );
        }

        if ($this->orderAddressSecurityProvider->isAddressGranted($order, AddressType::TYPE_SHIPPING)) {
            $builder
                ->add(
                    'shippingAddress',
                    OrderAddressType::NAME,
                    [
                        'label' => 'orob2b.order.shipping_address.label',
                        'order' => $options['data'],
                        'required' => false,
                        'addressType' => AddressType::TYPE_SHIPPING,
                    ]
                );
        }

        if ($this->isOverridePaymentTermGranted()) {
            $builder
                ->add(
                    'paymentTerm',
                    PaymentTermSelectType::NAME,
                    [
                        'label' => 'orob2b.order.payment_term.label',
                        'required' => false
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
                'data_class' => $this->dataClass,
            ]
        );
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return bool
     */
    protected function isOverridePaymentTermGranted()
    {
        return $this->securityFacade->isGranted('orob2b_order_payment_term_account_can_override');
    }
}
