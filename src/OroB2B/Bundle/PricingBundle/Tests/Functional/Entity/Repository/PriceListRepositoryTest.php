<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Functional\Entity\Repository;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\CustomerBundle\Entity\Customer;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListRepository;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

/**
 * @dbIsolation
 */
class PriceListRepositoryTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures(['OroB2B\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadPriceLists']);
    }

    public function testDefaultState()
    {
        $this->assertEquals([$this->getReference('price_list_3')], $this->getDefaultPriceLists());

        $repository = $this->getRepository();

        $repository->dropDefaults();
        $this->assertEquals([], $this->getDefaultPriceLists());

        /** @var PriceList $priceList1 */
        $priceList1 = $this->getReference('price_list_1');
        $repository->setDefault($priceList1);
        $this->assertEquals([$priceList1], $this->getDefaultPriceLists());

        /** @var PriceList $priceList2 */
        $priceList2 = $this->getReference('price_list_2');
        $repository->setDefault($priceList2);
        $this->assertEquals([$priceList2], $this->getDefaultPriceLists());

        $repository->dropDefaults();
        $this->assertEquals([], $this->getDefaultPriceLists());
    }

    public function testGetDefault()
    {
        $defaultPriceLists = $this->getDefaultPriceLists();

        $this->assertEquals(reset($defaultPriceLists), $this->getRepository()->getDefault());
    }

    /**
     * @return array|PriceList[]
     */
    public function getDefaultPriceLists()
    {
        return $this->getRepository()->findBy(['default' => true]);
    }

    public function testCustomerPriceList()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.orphan');

        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_3');

        $this->assertTrue($priceList->getCustomers()->contains($customer));

        $this->assertEquals(
            $priceList,
            $this->getRepository()->getPriceListByCustomer($customer)
        );

        /** @var PriceList $newPriceList */
        $newPriceList = $this->getReference('price_list_2');

        $this->getRepository()->setPriceListToCustomer($customer, $newPriceList);

        $this->getManager()->flush();

        $this->assertFalse($priceList->getCustomers()->contains($customer));
        $this->assertTrue($newPriceList->getCustomers()->contains($customer));

        $this->assertEquals(
            $newPriceList,
            $this->getRepository()->getPriceListByCustomer($customer)
        );
    }

    public function testCustomerGroupPriceList()
    {
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->getReference('customer_group.group1');

        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_1');

        $this->assertTrue($priceList->getCustomerGroups()->contains($customerGroup));

        $this->assertEquals(
            $priceList,
            $this->getRepository()->getPriceListByCustomerGroup($customerGroup)
        );

        /** @var PriceList $newPriceList */
        $newPriceList = $this->getReference('price_list_2');

        $this->getRepository()->setPriceListToCustomerGroup($customerGroup, $newPriceList);

        $this->getManager()->flush();

        $this->assertFalse($priceList->getCustomerGroups()->contains($customerGroup));
        $this->assertTrue($newPriceList->getCustomerGroups()->contains($customerGroup));

        $this->assertEquals(
            $newPriceList,
            $this->getRepository()->getPriceListByCustomerGroup($customerGroup)
        );
    }

    public function testWebsitePriceList()
    {
        /** @var Website $website */
        $website = $this->getReference('US');

        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_1');

        $this->assertTrue($priceList->getWebsites()->contains($website));

        $this->assertEquals(
            $priceList,
            $this->getRepository()->getPriceListByWebsite($website)
        );

        /** @var PriceList $newPriceList */
        $newPriceList = $this->getReference('price_list_2');

        $this->getRepository()->setPriceListToWebsite($website, $newPriceList);

        $this->getManager()->flush();

        $this->assertFalse($priceList->getWebsites()->contains($website));
        $this->assertTrue($newPriceList->getWebsites()->contains($website));

        $this->assertEquals(
            $newPriceList,
            $this->getRepository()->getPriceListByWebsite($website)
        );
    }

    /**
     * @return PriceListRepository
     */
    protected function getRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('OroB2BPricingBundle:PriceList');
    }

    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass('OroB2BPricingBundle:PriceList');
    }
}
