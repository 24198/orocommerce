<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Provider;

use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\ProductBundle\Provider\ProductTitleProvider;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Product;
use Oro\Component\WebCatalog\Entity\ContentVariantInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ProductTitleProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductTitleProvider
     */
    protected $productTitleProvider;

    protected function setUp()
    {
        $this->productTitleProvider = new ProductTitleProvider(PropertyAccess::createPropertyAccessor());
    }

    public function testGetTitle()
    {
        $product = new Product();
        $product->addName((new LocalizedFallbackValue())->setString('some title'));

        $contentVariant = $this
            ->getMockBuilder(ContentVariantInterface::class)
            ->setMethods(['getProductPageProduct', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $contentVariant
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(ProductTitleProvider::SUPPORTED_TYPE));

        $contentVariant
            ->expects($this->any())
            ->method('getProductPageProduct')
            ->will($this->returnValue($product));

        $this->assertEquals('some title', $this->productTitleProvider->getTitle($contentVariant));
    }

    public function testGetTitleWithNonDefaultTitleUse()
    {
        $page = new Product();

        $contentVariant = $this
            ->getMockBuilder(ContentVariantInterface::class)
            ->setMethods(['getProductPageProduct', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $contentVariant
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(ProductTitleProvider::SUPPORTED_TYPE));

        $contentVariant
            ->expects($this->any())
            ->method('getProductPageProduct')
            ->will($this->returnValue($page));

        $localization = new Localization();
        $localization->setName('de');

        $localizedValue = new LocalizedFallbackValue();
        $localizedValue->setString('some title');
        $localizedValue->setLocalization($localization);

        $page->addName($localizedValue);
        $this->assertEquals('some title', $this->productTitleProvider->getTitle($contentVariant));
    }

    public function testGetTitleForNonSupportedType()
    {
        $contentVariant = $this
            ->getMockBuilder(ContentVariantInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $contentVariant
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('__some_unsupported__'));

        $this->assertNull($this->productTitleProvider->getTitle($contentVariant));
    }
}
