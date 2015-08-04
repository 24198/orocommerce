<?php

namespace OroB2B\Bundle\OrderBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use OroB2B\Bundle\OrderBundle\Entity\Order;
use OroB2B\Bundle\OrderBundle\Entity\OrderAddress;
use OroB2B\Bundle\PaymentBundle\Entity\PaymentTerm;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $now = new \DateTime('now');
        $properties = [
            ['id', '123'],
            ['identifier', 'identifier-test-01'],
            ['owner', new User()],
            ['organization', new Organization()],
            ['shippingAddress', new OrderAddress()],
            ['billingAddress', new OrderAddress()],
            ['createdAt', $now, false],
            ['updatedAt', $now, false],
            ['poNumber', 'PO-#1'],
            ['customerNotes', 'customer notes'],
            ['shipUntil', $now],
            ['currency', 'USD'],
            ['subtotal', 999.99],
            ['paymentTerm', new PaymentTerm()],
        ];

        $this->assertPropertyAccessors(new Order(), $properties);
    }

    public function testPrePersist()
    {
        $order = new Order();
        $order->prePersist();
        $this->assertInstanceOf('\DateTime', $order->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $order->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $order = new Order();
        $order->preUpdate();
        $this->assertInstanceOf('\DateTime', $order->getUpdatedAt());
    }
}
