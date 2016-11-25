<?php

namespace Oro\Bundle\OrderBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadAccountUserData;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\OrderBundle\Form\Type\FrontendOrderType;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\PricingBundle\Entity\CombinedProductPrice;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Oro\Bundle\FrontendTestFrameworkBundle\Test\Client;

/**
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    const ORDER_PO_NUMBER = 'PO-NUMBER';
    const QUICK_ADD_ORDER_PO_NUMBER = 'QUICK-ADD-PO-NUMBER';
    const ORDER_PO_NUMBER_UPDATED = 'PO-NUMBER-UP';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var DateTimeFormatter
     */
    protected $dateFormatter;

    /**
     * @var NumberFormatter
     */
    protected $numberFormatter;

    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader(LoadAccountUserData::AUTH_USER, LoadAccountUserData::AUTH_PW)
        );

        $this->loadFixtures(
            [
                'Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders',
                'Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadCombinedProductPrices',
            ]
        );

        $this->dateFormatter = $this->getContainer()->get('oro_locale.formatter.date_time');
        $this->numberFormatter = $this->getContainer()->get('oro_locale.formatter.number');
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_frontend_index'));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('frontend-orders-grid', $crawler->html());
        $this->assertContains('Open Orders', $crawler->filter('h2.user-page-title')->first()->html());
        $this->assertContains('Past Orders', $crawler->filter('h2.user-page-title')->last()->html());
    }

    public function testOrdersGrid()
    {
        $response = $this->client->requestFrontendGrid('frontend-orders-grid');

        $result = static::getJsonResponseContent($response, 200);

        $myOrderData = [];
        foreach ($result['data'] as $row) {
            if ($row['identifier'] === LoadOrders::MY_ORDER) {
                $myOrderData = $row;
                break;
            }
        }

        $this->assertArrayHasKey('poNumber', $myOrderData);
        $this->assertEquals('PO_NUM', $myOrderData['poNumber']);
    }

    public function testCreate()
    {
        $this->markTestIncomplete('Should be fixed in scope of task BB-3686');
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_frontend_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $date = (new \DateTime('now'))->format('Y-m-d');

        /** @var Product $product */
        $product = $this->getReference('product.1');

        $lineItems = [
            [
                'product' => $product->getId(),
                'quantity' => 10,
                'productUnit' => 'liter',
                'shipBy' => $date,
            ],
        ];

        $submittedData = [
            'input_action' => 'save_and_stay',
            'oro_order_frontend_type' => [
                '_token' => $form['oro_order_frontend_type[_token]']->getValue(),
                'poNumber' => self::ORDER_PO_NUMBER,
                'shipUntil' => $date,
                'customerNotes' => 'Customer Notes',
                'lineItems' => $lineItems,
            ],
        ];

        $this->client->followRedirects(true);

        // Submit form
        $result = $this->client->getResponse();
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $submittedData);

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertViewPage($crawler, [
            self::ORDER_PO_NUMBER,
            'Customer Notes',
            $date,
        ]);

        /** @var CombinedProductPrice $productPrice */
        $productPrice = $this->getReference('product_price.1');
        $expectedLineItems = [
            [
                'product' => $product->getId(),
                'quantity' => 10,
                'productUnit' => 'liter',
                'price' => $this->formatProductPrice($productPrice),
                'shipBy' => $date,
            ],
        ];

        $this->assertEquals($expectedLineItems, $this->getActualLineItems($crawler, count($lineItems)));
    }

    /**
     * @depends testCreate
     * @return int
     */
    public function testUpdate()
    {
        $id = $this->findInGrid(
            'frontend-orders-grid',
            ['frontend-orders-grid[_filter][poNumber][value]' => self::ORDER_PO_NUMBER]
        );

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_order_frontend_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();

        /** @var Product $product */
        $product = $this->getReference('product.2');

        $date = (new \DateTime('now'))->format('Y-m-d');

        $lineItems = [
            [
                'product' => $product->getId(),
                'quantity' => 15,
                'productUnit' => 'liter',
                'shipBy' => $date,
            ],
        ];

        $submittedData = [
            'input_action' => 'save_and_stay',
            'oro_order_frontend_type' => [
                '_token' => $form['oro_order_frontend_type[_token]']->getValue(),
                'poNumber' => self::ORDER_PO_NUMBER_UPDATED,
                'lineItems' => $lineItems,
            ],
        ];

        $this->client->followRedirects(true);

        // Submit form
        $result = $this->client->getResponse();
        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        // Check updated order
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_frontend_update', ['id' => $id]));

        $this->assertEquals(
            self::ORDER_PO_NUMBER_UPDATED,
            $crawler->filter('input[name="oro_order_frontend_type[poNumber]"]')
                ->extract('value')[0]
        );

        /** @var CombinedProductPrice $productPrice */
        $productPrice = $this->getReference('product_price.1');
        $expectedLineItems = [
            [
                'product' => $product->getId(),
                'quantity' => 15,
                'productUnit' => 'liter',
                'price' => $this->formatProductPrice($productPrice),
                'shipBy' => $date,
            ],
        ];

        $this->assertEquals($expectedLineItems, $this->getActualLineItems($crawler, count($lineItems)));

        return $id;
    }

    /**
     * @depends testUpdate
     *
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_order_frontend_view', ['id' => $id]));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertViewPage($crawler, ['Notes']);
    }

    /**
     * @param Crawler $crawler
     * @param array $expectedViewData
     */
    public function assertViewPage(Crawler $crawler, array $expectedViewData)
    {
        $html = $crawler->html();
        foreach ($expectedViewData as $data) {
            $this->assertContains($data, $html);
        }
    }

    /**
     * @param array $orderData
     * @return array
     */
    protected function getFormData(array $orderData)
    {
        $result = [];
        foreach ($orderData as $field => $value) {
            $formFieldName = sprintf('%s[%s]', FrontendOrderType::NAME, $field);
            $result[$formFieldName] = $value;
        }

        return $result;
    }

    /**
     * @param string $gridName
     * @param array $filters
     * @return array
     */
    protected function findInGrid($gridName, array $filters)
    {
        $response = $this->client->requestFrontendGrid($gridName, $filters);

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        return $result['id'];
    }

    /**
     * @param Crawler $crawler
     * @param int $count
     * @param null $quickAdd
     * @return array
     */
    protected function getActualLineItems(Crawler $crawler, $count, $quickAdd = null)
    {
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $data = [
                'product' => $crawler->filter('input[name="oro_order_frontend_type[lineItems]['.$i.'][product]"]')
                    ->extract('value')[0],
                'quantity' => $crawler->filter('input[name="oro_order_frontend_type[lineItems]['.$i.'][quantity]"]')
                    ->extract('value')[0]
            ];

            if ($quickAdd) {
                $result[] = $data;
            } else {
                $result[] = array_merge(
                    $data,
                    [
                        'productUnit' => $crawler
                            ->filter(
                                'select[name="oro_order_frontend_type[lineItems]['.$i.'][productUnit]"] :selected'
                            )
                            ->html(),
                        'price' => trim(
                            $crawler->filter(
                                'tr[data-content="oro_order_frontend_type[lineItems]['
                                .$i.']"] .order-line-item-price-value'
                            )
                                ->html()
                        ),
                        'shipBy' => $crawler->filter(
                            'input[name="oro_order_frontend_type[lineItems]['.$i.'][shipBy]"]'
                        )
                            ->extract('value')[0]
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * @param CombinedProductPrice $productPrice
     * @return string
     */
    protected function formatProductPrice(CombinedProductPrice $productPrice)
    {
        $price = $productPrice->getPrice();

        return $this->numberFormatter->formatCurrency($price->getValue(), $price->getCurrency());
    }
}
