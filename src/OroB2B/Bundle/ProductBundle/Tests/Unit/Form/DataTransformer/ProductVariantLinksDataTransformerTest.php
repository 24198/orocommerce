<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Entity\ProductVariantLink;
use OroB2B\Bundle\ProductBundle\Form\DataTransformer\ProductVariantLinksDataTransformer;

class ProductVariantLinksDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductVariantLinksDataTransformer
     */
    protected $transformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->transformer = new ProductVariantLinksDataTransformer();
        $this->transformer->transform(new ArrayCollection());

    }

    public function testAddsLinkToEmptyCollection()
    {
        $product1 = new Product();
        $product2 = new Product();

        $links = $this->transformer->reverseTransform([
            'appendVariants' => [$product1, $product2],
            'removeVariants' => [],
        ]);

        $this->assertCount(2, $links);
        $this->assertEquals($product1, $links[0]->getProduct());
        $this->assertEquals($product2, $links[1]->getProduct());
    }

    public function testRemovesLinksFromCollection()
    {
        $parentProduct = new Product();

        $product1 = new Product();
        $product1->setSku('SKU1');
        $product2 = new Product();
        $product2->setSku('SKU2');

        $variantLink1 = new ProductVariantLink($parentProduct, $product1);
        $variantLink2 = new ProductVariantLink($parentProduct, $product2);

        $this->transformer->transform(new ArrayCollection([$variantLink1, $variantLink2]));

        $links = $this->transformer->reverseTransform([
            'appendVariants' => [],
            'removeVariants' => [$product1],
        ]);

        $this->assertCount(1, $links);
        $this->assertEquals($product2, $links->first()->getProduct());
    }

    public function testAddsAndRemovesLinksFromCollection()
    {
        $parentProduct = new Product();

        $product1 = new Product();
        $product1->setSku('SKU1');
        $product2 = new Product();
        $product2->setSku('SKU2');
        $product3 = new Product();
        $product3->setSku('SKU3');

        $variantLink1 = new ProductVariantLink($parentProduct, $product1);
        $variantLink2 = new ProductVariantLink($parentProduct, $product2);

        $this->transformer->transform(new ArrayCollection([$variantLink1, $variantLink2]));

        $links = $this->transformer->reverseTransform([
            'appendVariants' => [$product3],
            'removeVariants' => [$product1],
        ]);

        $this->assertCount(2, $links);
        $this->assertEquals($product2, $links->first()->getProduct());
        $this->assertEquals($product3, $links->last()->getProduct());
    }
}
