<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Symfony\Component\DomCrawler\Crawler;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\CustomerBundle\Entity\Customer;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CustomerControllerTest extends WebTestCase
{
    const CUSTOMER_NAME = 'Customer_name';
    const UPDATED_NAME = 'Customer_name_UP';

    protected function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));

        $this->loadFixtures(
            [
                'OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers',
                'OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups',
                'OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadInternalRating'
            ]
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orob2b_customer_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orob2b_customer_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Customer $parent */
        $parent = $this->getReference('customer.level_1');
        /** @var CustomerGroup $group */
        $group = $this->getReference('customer_group.group1');
        $internalRating = $this->getReference('internal_rating.1 of 5');
        $this->assertCustomerSave($crawler, self::CUSTOMER_NAME, $parent, $group, $internalRating);
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'customer-customers-grid',
            ['customer-customers-grid[_filter][name][value]' => self::CUSTOMER_NAME]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orob2b_customer_update', ['id' => $result['id']])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Customer $newParent */
        $newParent = $this->getReference('customer.level_1.1');
        /** @var CustomerGroup $newGroup */
        $newGroup = $this->getReference('customer_group.group2');
        $internalRating = $this->getReference('internal_rating.2 of 5');
        $this->assertCustomerSave($crawler, self::UPDATED_NAME, $newParent, $newGroup, $internalRating);

        return $id;
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orob2b_customer_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        $this->assertContains(self::UPDATED_NAME . ' - Customers - Customers', $html);
        /** @var Customer $newParent */
        $newParent = $this->getReference('customer.level_1.1');
        /** @var CustomerGroup $newGroup */
        $newGroup = $this->getReference('customer_group.group2');
        $internalRating = $this->getReference('internal_rating.2 of 5');
        $this->assertViewPage($html, self::UPDATED_NAME, $newParent, $newGroup, $internalRating);
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     * @param Customer $parent
     * @param CustomerGroup $group
     */
    protected function assertCustomerSave(
        Crawler $crawler,
        $name,
        Customer $parent,
        CustomerGroup $group,
        AbstractEnumValue $internalRating
    ) {
        $form = $crawler->selectButton('Save and Close')->form(
            [
                'orob2b_customer_type[name]' => $name,
                'orob2b_customer_type[parent]' => $parent->getId(),
                'orob2b_customer_type[group]' => $group->getId(),
                'orob2b_customer_type[internal_rating]' => $internalRating->getId(),

            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains('Customer has been saved', $html);
        $this->assertViewPage($html, $name, $parent, $group, $internalRating);
    }

    /**
     * @param string $html
     * @param string $name
     * @param Customer $parent
     * @param CustomerGroup $group
     */
    protected function assertViewPage(
        $html,
        $name,
        Customer $parent,
        CustomerGroup $group,
        AbstractEnumValue $internalRating
    ) {
        $this->assertContains($name, $html);
        $this->assertContains($parent->getName(), $html);
        $this->assertContains($group->getName(), $html);
        $this->assertContains($internalRating->getName(), $html);
    }
}
