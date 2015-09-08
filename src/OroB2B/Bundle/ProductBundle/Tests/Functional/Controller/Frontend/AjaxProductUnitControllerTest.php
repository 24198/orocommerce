<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Functional\Controller\Frontend;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;

/**
 * @dbIsolation
 */
class AjaxProductUnitControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures(['OroB2B\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductUnitPrecisions']);
    }

    /**
     * @param string $productReference
     * @param array $expectedData
     *
     * @dataProvider productUnitsDataProvider
     */
    public function testProductUnitsAction($productReference, array $expectedData)
    {
        $product = $this->getProduct($productReference);

        $this->client->request(
            'GET',
            $this->getUrl('orob2b_product_frontend_ajaxproductunit_productunits', ['id' => $product->getId()])
        );

        $result = $this->client->getResponse();
        static::assertJsonResponseStatusCodeEquals($result, 200);

        $data = json_decode($result->getContent(), true);

        static::assertArrayHasKey('units', $data);
        static::assertEquals($expectedData, $data['units']);
    }

    /**
     * @return array
     */
    public function productUnitsDataProvider()
    {
        return [
            [
                'product.1',
                [
                    'bottle' => 'orob2b.product_unit.bottle.label.full',
                    'liter' => 'orob2b.product_unit.liter.label.full',
                ],
            ],
            [
                'product.2',
                [
                    'bottle' => 'orob2b.product_unit.bottle.label.full',
                    'box' => 'orob2b.product_unit.box.label.full',
                    'liter' => 'orob2b.product_unit.liter.label.full',
                ],
            ],
        ];
    }

    /**
     * @param string $reference
     * @return Product
     */
    protected function getProduct($reference)
    {
        return $this->getReference($reference);
    }

    /**
     * @param string $reference
     * @return ProductUnit
     */
    protected function getProductUnit($reference)
    {
        return $this->getReference($reference);
    }
}
