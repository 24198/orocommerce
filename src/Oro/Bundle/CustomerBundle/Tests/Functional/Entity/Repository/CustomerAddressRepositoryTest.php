<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;

/**
 * @dbIsolation
 */
class CustomerAddressRepositoryTest extends WebTestCase
{
    /**
     * @var CustomerAddressRepository
     */
    protected $repository;

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->repository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerAddress');

        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountAddresses'
            ]
        );
    }

    /**
     * @dataProvider addressesDataProvider
     * @param string $accountReference
     * @param string $type
     * @param array $expectedAddressReferences
     */
    public function testGetAddressesByType($accountReference, $type, array $expectedAddressReferences)
    {
        /** @var Customer $account */
        $account = $this->getReference($accountReference);

        /** @var CustomerAddress[] $actual */
        $actual = $this->repository->getAddressesByType(
            $account,
            $type,
            $this->getContainer()->get('oro_security.acl_helper')
        );
        $this->assertCount(count($expectedAddressReferences), $actual);
        $addressIds = [];
        foreach ($actual as $address) {
            $addressIds[] = $address->getId();
        }
        foreach ($expectedAddressReferences as $addressReference) {
            $this->assertContains($this->getReference($addressReference)->getId(), $addressIds);
        }
    }

    /**
     * @return array
     */
    public function addressesDataProvider()
    {
        return [
            [
                'account.level_1',
                'billing',
                [
                    'account.level_1.address_1',
                    'account.level_1.address_2',
                    'account.level_1.address_3'
                ]
            ],
            [
                'account.level_1',
                'shipping',
                [
                    'account.level_1.address_1',
                    'account.level_1.address_3'
                ]
            ]
        ];
    }

    /**
     * @dataProvider defaultAddressDataProvider
     * @param string $accountReference
     * @param string $type
     * @param string $expectedAddressReference
     */
    public function testGetDefaultAddressesByType($accountReference, $type, $expectedAddressReference)
    {
        /** @var Customer $account */
        $account = $this->getReference($accountReference);

        /** @var CustomerAddress[] $actual */
        $actual = $this->repository->getDefaultAddressesByType(
            $account,
            $type,
            $this->getContainer()->get('oro_security.acl_helper')
        );
        $this->assertCount(1, $actual);
        $this->assertEquals($this->getReference($expectedAddressReference)->getId(), $actual[0]->getId());
    }

    /**
     * @return array
     */
    public function defaultAddressDataProvider()
    {
        return [
            [
                'account.level_1',
                'billing',
                'account.level_1.address_2'
            ],
            [
                'account.level_1',
                'shipping',
                'account.level_1.address_1'
            ]
        ];
    }
}
