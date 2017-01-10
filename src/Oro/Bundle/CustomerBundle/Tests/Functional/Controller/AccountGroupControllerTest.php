<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DomCrawler\Crawler;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

/**
 * @dbIsolation
 */
class AccountGroupControllerTest extends WebTestCase
{
    const NAME = 'Group_name';
    const UPDATED_NAME = 'Group_name_UP';
    const ADD_NOTE_BUTTON = 'Add note';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->entityManager = $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerGroup');

        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccounts',
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups'
            ]
        );
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_account_group_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('account-groups-grid', $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_customer_account_group_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertAccountGroupSave(
            $crawler,
            self::NAME,
            [
                $this->getReference('account.level_1.1'),
                $this->getReference('account.level_1.2')
            ]
        );
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $id = $this->getGroupId(self::NAME);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_customer_account_group_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertAccountGroupSave(
            $crawler,
            self::UPDATED_NAME,
            [
                $this->getReference('account.level_1.1.1')
            ],
            [
                $this->getReference('account.level_1.2')
            ]
        );

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
            $this->getUrl('oro_customer_account_group_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();
        $this->assertContains(self::UPDATED_NAME . ' - Customer Groups - Customers', $html);
        $this->assertContains(self::ADD_NOTE_BUTTON, $html);
        $this->assertViewPage($html, self::UPDATED_NAME);
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     * @param Customer[] $appendAccounts
     * @param Customer[] $removeAccounts
     */
    protected function assertAccountGroupSave(
        Crawler $crawler,
        $name,
        array $appendAccounts = [],
        array $removeAccounts = []
    ) {
        $appendAccountIds = array_map(
            function (Customer $account) {
                return $account->getId();
            },
            $appendAccounts
        );
        $removeAccountIds = array_map(
            function (Customer $account) {
                return $account->getId();
            },
            $removeAccounts
        );
        $form = $crawler->selectButton('Save and Close')->form(
            [
                'oro_account_group_type[name]' => $name,
                'oro_account_group_type[appendAccounts]' => implode(',', $appendAccountIds),
                'oro_account_group_type[removeAccounts]' => implode(',', $removeAccountIds)
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains('Customer group has been saved', $html);
        $this->assertViewPage($html, $name);

        foreach ($appendAccounts as $account) {
            $this->assertContains($account->getName(), $html);
        }
        foreach ($removeAccounts as $account) {
            $this->assertNotContains($account->getName(), $html);
        }
    }

    /**
     * @param string $html
     * @param string $name
     */
    protected function assertViewPage($html, $name)
    {
        $this->assertContains($name, $html);
    }

    /**
     * @param string $name
     * @return int
     */
    protected function getGroupId($name)
    {
        /** @var CustomerGroup $accountGroup */
        $accountGroup = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:CustomerGroup')
            ->getRepository('OroCustomerBundle:CustomerGroup')
            ->findOneBy(['name' => $name]);

        return $accountGroup->getId();
    }
}
