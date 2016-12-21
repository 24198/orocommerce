<?php

namespace Oro\Bundle\OrderBundle\Tests\Unit\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\AccountAddress;
use Oro\Bundle\CustomerBundle\Entity\AccountUser;
use Oro\Bundle\CustomerBundle\Entity\AccountUserAddress;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\OrderBundle\Manager\OrderAddressManager;
use Oro\Bundle\OrderBundle\Provider\OrderAddressProvider;

class OrderAddressManagerTest extends AbstractAddressManagerTest
{
    /** @var OrderAddressManager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderAddressProvider */
    protected $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry */
    protected $registry;

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('Oro\Bundle\OrderBundle\Provider\OrderAddressProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->manager = new OrderAddressManager(
            $this->provider,
            $this->registry,
            'Oro\Bundle\OrderBundle\Entity\OrderAddress'
        );
    }

    protected function tearDown()
    {
        unset($this->manager, $this->provider, $this->registry);
    }

    /**
     * @param AbstractAddress $address
     * @param OrderAddress|null $expected
     * @param AbstractAddress|null $expectedAccountAddress
     * @param AbstractAddress|null $expectedAccountUserAddress
     * @param OrderAddress|null $orderAddress
     *
     * @dataProvider orderDataProvider
     */
    public function testUpdateFromAbstract(
        AbstractAddress $address,
        OrderAddress $expected = null,
        AbstractAddress $expectedAccountAddress = null,
        AbstractAddress $expectedAccountUserAddress = null,
        OrderAddress $orderAddress = null
    ) {
        $classMetadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())->method('getFieldNames')->willReturn(['street', 'city', 'label']);
        $classMetadata->expects($this->once())->method('getAssociationNames')
            ->willReturn(['country', 'region']);

        $em = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->once())->method('getClassMetadata')->willReturn($classMetadata);

        $this->registry->expects($this->any())->method('getManagerForClass')->with($this->isType('string'))
            ->willReturn($em);

        $orderAddress = $this->manager->updateFromAbstract($address, $orderAddress);
        $this->assertEquals($expected, $orderAddress);
        $this->assertEquals($expectedAccountAddress, $orderAddress->getAccountAddress());
        $this->assertEquals($expectedAccountUserAddress, $orderAddress->getAccountUserAddress());
    }

    /**
     * @return array
     */
    public function orderDataProvider()
    {
        $country = new Country('US');
        $region = new Region('US-AL');

        return [
            'empty account address' => [
                $accountAddress = new AccountAddress(),
                (new OrderAddress())
                    ->setAccountAddress($accountAddress),
                $accountAddress
            ],
            'empty account user address' => [
                $accountUserAddress = new AccountUserAddress(),
                (new OrderAddress())
                    ->setAccountUserAddress($accountUserAddress),
                null,
                $accountUserAddress
            ],
            'from account address' => [
                $accountAddress = (new AccountAddress())
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                (new OrderAddress())
                    ->setAccountAddress($accountAddress)
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                $accountAddress
            ],
            'from account user address' => [
                $accountUserAddress = (new AccountUserAddress())
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                (new OrderAddress())
                    ->setAccountUserAddress($accountUserAddress)
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                null,
                $accountUserAddress
            ],
            'do not override value from existing with empty one' => [
                $accountUserAddress = (new AccountUserAddress())
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                (new OrderAddress())
                    ->setAccountUserAddress($accountUserAddress)
                    ->setLabel('ExistingLabel')
                    ->setCountry($country)
                    ->setRegion($region)
                    ->setStreet('Street')
                    ->setCity('City'),
                null,
                $accountUserAddress,
                (new OrderAddress())
                    ->setLabel('ExistingLabel')
            ],
        ];
    }

    /**
     * @param Order $order
     * @param array $accountAddresses
     * @param array $accountUserAddresses
     * @param array $expected
     *
     * @dataProvider groupedAddressDataProvider
     */
    public function testGetGroupedAddresses(
        Order $order,
        array $accountAddresses = [],
        array $accountUserAddresses = [],
        array $expected = []
    ) {
        $this->provider->expects($this->any())->method('getAccountAddresses')->willReturn($accountAddresses);
        $this->provider->expects($this->any())->method('getAccountUserAddresses')->willReturn($accountUserAddresses);

        $this->manager->addEntity('au', 'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress');
        $this->manager->addEntity('a', 'Oro\Bundle\CustomerBundle\Entity\AccountAddress');

        $this->assertEquals($expected, $this->manager->getGroupedAddresses($order, AddressType::TYPE_BILLING));
    }

    /**
     * @return array
     */
    public function groupedAddressDataProvider()
    {
        return [
            'empty account user' => [new Order()],
            'empty account' => [
                (new Order())->setAccountUser(new AccountUser()),
                [],
                [
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountUserAddress', 1),
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountUserAddress', 2),
                ],
                [
                    OrderAddressManager::ACCOUNT_USER_LABEL => [
                        'au_1' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress',
                            1
                        ),
                        'au_2' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress',
                            2
                        ),
                    ],
                ],
            ],
            'account' => [
                (new Order())->setAccountUser(new AccountUser())->setAccount(new Account()),
                [
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountAddress', 1),
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountAddress', 2),
                ],
                [],
                [
                    OrderAddressManager::ACCOUNT_LABEL => [
                        'a_1' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountAddress',
                            1
                        ),
                        'a_2' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountAddress',
                            2
                        ),
                    ],
                ],
            ],
            'full' => [
                (new Order())->setAccountUser(new AccountUser())->setAccount(new Account()),
                [
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountAddress', 1),
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountAddress', 2),
                ],
                [
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountUserAddress', 1),
                    $this->getEntity('Oro\Bundle\CustomerBundle\Entity\AccountUserAddress', 2),
                ],
                [
                    OrderAddressManager::ACCOUNT_LABEL => [
                        'a_1' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountAddress',
                            1
                        ),
                        'a_2' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountAddress',
                            2
                        ),
                    ],
                    OrderAddressManager::ACCOUNT_USER_LABEL => [
                        'au_1' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress',
                            1
                        ),
                        'au_2' => $this->getEntity(
                            'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress',
                            2
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Order $order
     * @param array $accountAddresses
     * @param array $accountUserAddresses
     * @param array $addresses
     *
     * @dataProvider groupedAddressDataProvider
     */
    public function testGetAddressTypes(
        Order $order,
        array $accountAddresses = [],
        array $accountUserAddresses = [],
        array $addresses = []
    ) {
        $accountManager = $this->getManager(
            $accountAddresses,
            $this->getTypes($accountAddresses, ['billing'])
        );
        $accountUserManager = $this->getManager(
            $accountUserAddresses,
            $this->getTypes($accountUserAddresses, ['billing', 'shipping'])
        );

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturnMap(
                [
                    ['OroCustomerBundle:AccountAddressToAddressType', $accountManager],
                    ['OroCustomerBundle:AccountUserAddressToAddressType', $accountUserManager]
                ]
            );

        $expectedTypes = [];
        if (array_key_exists(OrderAddressManager::ACCOUNT_LABEL, $addresses)) {
            foreach ($addresses[OrderAddressManager::ACCOUNT_LABEL] as $id => $address) {
                $expectedTypes[$id] = ['billing'];
            }
        }
        if (array_key_exists(OrderAddressManager::ACCOUNT_USER_LABEL, $addresses)) {
            foreach ($addresses[OrderAddressManager::ACCOUNT_USER_LABEL] as $id => $address) {
                $expectedTypes[$id] = ['billing', 'shipping'];
            }
        }

        $this->manager->addEntity('au', 'Oro\Bundle\CustomerBundle\Entity\AccountUserAddress');
        $this->manager->addEntity('a', 'Oro\Bundle\CustomerBundle\Entity\AccountAddress');
        $this->assertEquals($expectedTypes, $this->manager->getAddressTypes($addresses));
    }

    /**
     * @param array $addresses
     * @param array $types
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getManager(array $addresses, $types)
    {
        $repo = $this->createMock('\Doctrine\Common\Persistence\ObjectRepository');
        $manager = $this->createMock('\Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repo);
        $repo->expects($this->any())
            ->method('findBy')
            ->with(['address' => $addresses])
            ->willReturn($types);

        return $manager;
    }

    /**
     * @param array $addresses
     * @param array $types
     * @return array
     */
    protected function getTypes(array $addresses, array $types)
    {
        $result = [];
        foreach ($addresses as $address) {
            foreach ($types as $type) {
                $typeEntity = new AddressType($type);
                $typeToEntity = $this
                    ->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\AbstractAddressToAddressType')
                    ->disableOriginalConstructor()
                    ->setMethods(['getAddress', 'getType'])
                    ->getMockForAbstractClass();
                $typeToEntity->expects($this->any())
                    ->method('getAddress')
                    ->willReturn($address);
                $typeToEntity->expects($this->any())
                    ->method('getType')
                    ->willReturn($typeEntity);
                $result[] = $typeToEntity;
            }
        }

        return $result;
    }
}
