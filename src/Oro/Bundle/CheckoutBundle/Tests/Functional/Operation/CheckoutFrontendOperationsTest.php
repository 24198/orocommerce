<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Functional\Operation;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Tests\Functional\DataFixtures\LoadQuoteCompletedCheckoutsData;
use Oro\Bundle\FrontendBundle\Tests\Functional\FrontendActionTestCase;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders;

/**
 * @dbIsolation
 */
class CheckoutFrontendOperationsTest extends FrontendActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadCustomerUserData::AUTH_USER, LoadCustomerUserData::AUTH_PW)
        );
        $this->loadFixtures(
            [
                LoadQuoteCompletedCheckoutsData::class
            ]
        );
    }

    public function testCheckoutViewOrderOperation()
    {
        $checkout = $this->getReference(LoadQuoteCompletedCheckoutsData::CHECKOUT_1);

        $this->executeOperation($checkout, 'oro_checkout_frontend_view_order');
        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('redirectUrl', $data);
        $this->assertTrue($data['success']);

        $crawler = $this->client->request('GET', $data['redirectUrl']);
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $this->assertContains('Order #' . LoadOrders::ORDER_1, $crawler->html());
    }

    /**
     * @param Checkout $checkout
     * @param string $operationName
     */
    protected function executeOperation(Checkout $checkout, $operationName)
    {
        $this->assertExecuteOperation(
            $operationName,
            $checkout->getId(),
            Checkout::class,
            ['datagrid' => 'frontend-checkouts-grid', 'group' => ['datagridRowAction']]
        );
    }
}