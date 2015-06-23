<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Unit\Entity;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\SaleBundle\Entity\Quote;
use OroB2B\Bundle\SaleBundle\Entity\QuoteProduct;
use OroB2B\Bundle\SaleBundle\Entity\QuoteProductItem;

class QuoteProductTest extends AbstractTest
{
    public function testProperties()
    {
        $properties = [
            ['id', 123],
            ['quote', new Quote()],
            ['product', new Product()],
            ['productSku', 'sku'],
        ];

        $this->assertPropertyAccessors(new QuoteProduct(), $properties);
    }

    public function testSetProduct()
    {
        $product = new QuoteProduct();

        $this->assertNull($product->getProductSku());

        $product->setProduct((new Product)->setSku('test-sku'));

        $this->assertEquals('test-sku', $product->getProductSku());
    }

    public function testAddQuoteProductItem()
    {
        $quoteProduct       = new QuoteProduct();
        $quoteProductItem   = new QuoteProductItem();

        $this->assertNull($quoteProductItem->getQuoteProduct());

        $quoteProduct->addQuoteProductItem($quoteProductItem);

        $this->assertEquals($quoteProduct, $quoteProductItem->getQuoteProduct());
    }

    public function testQuoteProductItems()
    {
        $this->assertCollection(new QuoteProduct(), new QuoteProductItem());
    }
}
