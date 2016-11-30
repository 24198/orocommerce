<?php

namespace Oro\Bundle\CheckoutBundle\Bundle\Tests\Unit\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CheckoutBundle\DataProvider\Manager\CheckoutLineItemsManager;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Factory\CheckoutShippingContextFactory;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\AccountUser;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PricingBundle\SubtotalProcessor\Model\Subtotal;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;
use Oro\Bundle\ShippingBundle\Context\ShippingContext;
use Oro\Bundle\ShippingBundle\Factory\ShippingContextFactory;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;

class ShippingContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var CheckoutShippingContextFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $factory;

    /** @var  ShoppingList|\PHPUnit_Framework_MockObject_MockObject */
    protected $shoppingList;

    /** @var  CheckoutLineItemsManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutLineItemsManager;

    /** @var  TotalProcessorProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $totalProcessorProvider;

    /** @var  ShippingContextFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $shippingContextFactory;

    protected function setUp()
    {
        $this->shoppingList = $this->getMockBuilder(ShoppingList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutLineItemsManager = $this->getMockBuilder(CheckoutLineItemsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->totalProcessorProvider = $this->getMockBuilder(TotalProcessorProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shippingContextFactory = $this->getMockBuilder(ShippingContextFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new CheckoutShippingContextFactory(
            $this->checkoutLineItemsManager,
            $this->totalProcessorProvider,
            $this->shippingContextFactory
        );
    }

    protected function tearDown()
    {
        unset(
            $this->factory,
            $this->checkout,
            $this->shoppingList,
            $this->checkoutLineItemsManager,
            $this->totalProcessorProvider,
            $this->shippingContextFactory
        );
    }


    public function testCreate()
    {
        /** @var AddressInterface $address */
        $address = $this->getMock(OrderAddress::class);
        $currency = 'USD';
        $paymentMethod = 'SomePaymentMethod';
        $amount = 100;
        $customer = new Account();
        $customerUser = new AccountUser();

        $subtotal = (new Subtotal())
            ->setAmount($amount)
            ->setCurrency($currency);

        $checkout = (new Checkout())
            ->setBillingAddress($address)
            ->setShippingAddress($address)
            ->setCurrency($currency)
            ->setPaymentMethod($paymentMethod)
            ->setAccount($customer)
            ->setAccountUser($customerUser);

        $context = new ShippingContext();
        $context->setSourceEntity($checkout);
        $context->setSourceEntityIdentifier($checkout->getId());
        $context->setBillingAddress($address);
        $context->setShippingAddress($address);
        $context->setCurrency($currency);
        $context->setPaymentMethod($paymentMethod);
        $context->setSubtotal(Price::create($amount, $currency));
        $context->setCustomer($customer);
        $context->setCustomerUser($customerUser);

        $this->checkoutLineItemsManager
            ->expects(static::once())
            ->method('getData')
            ->willReturn(new ArrayCollection());

        $this->shippingContextFactory
            ->expects(static::once())
            ->method('create')
            ->willReturn(new ShippingContext());


        $this->totalProcessorProvider
            ->expects(static::once())
            ->method('getTotal')
            ->with($checkout)
            ->willReturn($subtotal);

        static::assertEquals($context, $this->factory->create($checkout));
    }
}
