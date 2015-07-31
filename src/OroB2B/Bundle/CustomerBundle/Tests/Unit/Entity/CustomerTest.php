<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\CustomerBundle\Entity\Customer;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;
use OroB2B\Bundle\CustomerBundle\Tests\Unit\Traits\AddressEntityTestTrait;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    use AddressEntityTestTrait;

    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $this->assertPropertyAccessors($this->createCustomerEntity(), [
            ['id', 42],
            ['name', 'Adam Weishaupt'],
            ['parent', $this->createCustomerEntity()],
            ['group', $this->createCustomerGroupEntity()],
            ['organization', $this->createOrganization()],
        ]);
    }

    public function testToString()
    {
        $customer = new Customer();

        $this->assertSame('', (string)$customer);

        $customer->setName(123);

        $this->assertSame('123', (string)$customer);
    }

    /**
     * Test children
     */
    public function testChildrenCollection()
    {
        $parentCustomer = $this->createCustomerEntity();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $parentCustomer->getChildren());
        $this->assertCount(0, $parentCustomer->getChildren());

        $customer = $this->createCustomerEntity();

        $this->assertInstanceOf(
            'OroB2B\Bundle\CustomerBundle\Entity\Customer',
            $parentCustomer->addChild($customer)
        );

        $this->assertCount(1, $parentCustomer->getChildren());

        $parentCustomer->addChild($customer);

        $this->assertCount(1, $parentCustomer->getChildren());

        $parentCustomer->removeChild($customer);

        $this->assertCount(0, $parentCustomer->getChildren());
    }

    /**
     * Test users
     */
    public function testUsersCollection()
    {
        $customer = $this->createCustomerEntity();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $customer->getUsers());
        $this->assertCount(0, $customer->getUsers());

        $user = $this->createUserEntity();

        $customer->addUser($user);
        $this->assertEquals([$user], $customer->getUsers()->toArray());

        // entity added only once
        $customer->addUser($user);
        $this->assertEquals([$user], $customer->getUsers()->toArray());

        $customer->removeUser($user);
        $this->assertCount(0, $customer->getUsers());

        // undefined user can't be removed
        $customer->removeUser($user);
        $this->assertCount(0, $customer->getUsers());
    }

    /**
     * @return CustomerGroup
     */
    protected function createCustomerGroupEntity()
    {
        return new CustomerGroup();
    }

    /**
     * @return Customer
     */
    protected function createCustomerEntity()
    {
        return new Customer();
    }

    /**
     * @return AccountUser
     */
    protected function createUserEntity()
    {
        return new AccountUser();
    }

    /**
     * @return Organization
     */
    protected function createOrganization()
    {
        return new Organization();
    }

    /**
     * @return CustomerAddress
     */
    protected function createAddressEntity()
    {
        return new CustomerAddress();
    }

    /**
     * @return Customer
     */
    protected function createTestedEntity()
    {
        return $this->createCustomerEntity();
    }
}
