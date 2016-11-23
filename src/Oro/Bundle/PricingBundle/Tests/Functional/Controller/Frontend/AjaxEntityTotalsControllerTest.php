<?php

namespace Oro\Bundle\PricingBundle\Tests\Functional\Controller\Frontend;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfig;
use Oro\Bundle\CustomerBundle\Entity\AccountUser;
use Oro\Bundle\CustomerBundle\Entity\AccountUserSettings;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadAccountUserData;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Tests\Functional\DataFixtures\LoadShoppingLists;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * @dbIsolation
 */
class AjaxEntityTotalsControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadAccountUserData::AUTH_USER, LoadAccountUserData::AUTH_PW)
        );

        $this->loadFixtures(
            [
                'Oro\Bundle\ShoppingListBundle\Tests\Functional\DataFixtures\LoadShoppingListLineItems',
                'Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadCombinedProductPrices',
            ]
        );
    }

    public function testEntityTotalsActionForShoppingList()
    {
        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getReference(LoadShoppingLists::SHOPPING_LIST_1);

        // set account user not default currency
        $manager = $this->getContainer()->get('oro_config.manager');
        $manager->set(CurrencyConfig::getConfigKeyByName(CurrencyConfig::KEY_DEFAULT_CURRENCY), 'EUR');
        $user = $this->getCurrentUser();
        $website = $this->getCurrentWebsite();
        $settings = new AccountUserSettings($website);
        $settings->setCurrency('EUR');
        $user->setWebsiteSettings($settings);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($settings);
        $em->flush();

        $classNameHelper = $this->getContainer()->get('oro_entity.entity_class_name_helper');

        $params = [
            'entityClassName' => $classNameHelper->resolveEntityClass(ClassUtils::getClass($shoppingList)),
            'entityId' => $shoppingList->getId()
        ];

        $this->client->request('GET', $this->getUrl('oro_pricing_frontend_entity_totals', $params));

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        $data = json_decode($result->getContent(), true);

        $this->assertArrayHasKey('total', $data);
        $this->assertEquals($data['total']['amount'], 282.43);
        $this->assertEquals($data['total']['currency'], 'EUR');

        $this->assertArrayHasKey('subtotals', $data);
        $this->assertEquals(282.43, $data['subtotals'][0]['amount']);
        $this->assertEquals('EUR', $data['subtotals'][0]['currency']);
    }

    public function testGetEntityTotalsAction()
    {
        $this->client->request('GET', $this->getUrl('oro_pricing_frontend_entity_totals'));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    public function testRecalculateTotalsAction()
    {
        $this->client->request('POST', $this->getUrl('oro_pricing_frontend_recalculate_entity_totals'));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    /**
     * @return AccountUser
     */
    protected function getCurrentUser()
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository('OroCustomerBundle:AccountUser')
            ->findOneBy(['username' => LoadAccountUserData::AUTH_USER]);
    }

    /**
     * @return Website
     */
    protected function getCurrentWebsite()
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository('OroWebsiteBundle:Website')
            ->find(1);
    }
}
