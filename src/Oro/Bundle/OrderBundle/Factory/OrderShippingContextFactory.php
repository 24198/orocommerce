<?php

namespace Oro\Bundle\OrderBundle\Factory;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\ShippingBundle\Context\ShippingContext;
use Oro\Bundle\ShippingBundle\Factory\ShippingContextFactory;

class OrderShippingContextFactory
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var ShippingContextFactory|null
     */
    protected $shippingContextFactory;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ShippingContextFactory|null $shippingContextFactory
     */
    public function __construct(DoctrineHelper $doctrineHelper, ShippingContextFactory $shippingContextFactory = null)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->shippingContextFactory = $shippingContextFactory;
    }

    /**
     * @param Order $order
     * @return ShippingContext
     */
    public function create(Order $order)
    {
        if (!$this->shippingContextFactory) {
            return null;
        }
        $shippingContext = $this->shippingContextFactory->create();

        $shippingContext->setShippingAddress($order->getShippingAddress());
        $shippingContext->setBillingAddress($order->getBillingAddress());
        $shippingContext->setCurrency($order->getCurrency());
        $shippingContext->setSubtotal(Price::create($order->getSubtotal(), $order->getCurrency()));
        $shippingContext->setSourceEntity($order);
        $shippingContext->setSourceEntityIdentifier($order->getId());

        if ($order->getLineItems()) {
            $shippingContext->setLineItems($order->getLineItems()->toArray());
        }

        /** @var PaymentTransactionRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class);
        /** @var PaymentTransaction $paymentTransaction */
        $paymentTransaction = $repository->findOneBy([
            'entityClass' => Order::class,
            'entityIdentifier' => $order->getId()
        ]);
        if ($paymentTransaction instanceof PaymentTransaction) {
            $shippingContext->setPaymentMethod($paymentTransaction->getPaymentMethod());
        }

        return $shippingContext;
    }
}
