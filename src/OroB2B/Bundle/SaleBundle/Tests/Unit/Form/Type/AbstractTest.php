<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;

use Oro\Bundle\CurrencyBundle\Model\Price;
use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;

use OroB2B\Bundle\SaleBundle\Entity\QuoteProduct;
use OroB2B\Bundle\SaleBundle\Entity\QuoteProductItem;

abstract class AbstractTest extends FormIntegrationTestCase
{
    /**
     * @param bool $isValid
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($isValid, $submittedData, $expectedData, $defaultData = null) {

        $form = $this->factory->create($this->formType, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);

        $this->assertEquals($isValid, $form->isValid());

        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    abstract public function submitProvider();

    /**
     * @return PriceType
     */
    protected function preparePriceType()
    {
        $price = new PriceType();
        $price->setDataClass('Oro\Bundle\CurrencyBundle\Model\Price');

        return $price;
    }

//    /**
//     * @return CurrencySelectionType
//     */
//    protected function prepareCurrencySelectionType()
//    {
//        /* @var $configManager \PHPUnit_Framework_MockObject_MockBuilder|ConfigManager */
//        $configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $configManager->expects($this->any())
//            ->method('get')
//            ->with('oro_currency.allowed_currencies')
//            ->will($this->returnValue(['USD', 'EUR', 'XX']));
//
//        /* @var $localeSettings \PHPUnit_Framework_MockObject_MockBuilder|LocaleSettings */
//        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        return new CurrencySelectionType($configManager, $localeSettings);
//    }

    /**
     * @return EntityType
     */
    protected function prepareProductEntityType()
    {
        $entityType = new EntityType(
            [
                2 => $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', 2),
                3 => $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', 3),
            ]
        );

        return $entityType;
    }

    /**
     * @return EntityType
     */
    protected function prepareProductUnitSelectionType()
    {
        $productUnitSelectionType = new EntityType(
            [
                'kg'    => $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\ProductUnit', 'kg', 'code'),
                'item'  => $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\ProductUnit', 'item', 'code'),
            ],
            ProductUnitSelectionType::NAME
        );

        return $productUnitSelectionType;
    }

    /**
     * @param string $className
     * @param int $id
     * @param string $primaryKey
     * @return object
     */
    protected function getEntity($className, $id, $primaryKey = 'id')
    {
        static $entities = [];

        if (!isset($entities[$className])) {
            $entities[$className] = [];
        }

        if (!isset($entities[$className][$id])) {
            $entities[$className][$id] = new $className;
            $reflectionClass = new \ReflectionClass($className);
            $method = $reflectionClass->getProperty($primaryKey);
            $method->setAccessible(true);
            $method->setValue($entities[$className][$id], $id);
        }

        return $entities[$className][$id];
    }

    /**
     * @param int $productId
     * @param QuoteProductItem[] $items
     * @return QuoteProduct
     */
    protected function getQuoteProduct($productId, $items = [])
    {
        $quoteProduct = new QuoteProduct();
        $quoteProduct->setProduct($this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', $productId));

        foreach ($items as $item) {
            $quoteProduct->addQuoteProductItem($item);
        }

        return $quoteProduct;
    }

    /**
     * @param float $quantity
     * @param string $unitCode
     * @param Price $price
     * @return QuoteProductItem
     */
    protected function getQuoteProductItem($quantity = null, $unitCode = null, Price $price = null)
    {
        $quoteProductItem = new QuoteProductItem();

        if (null !== $quantity) {
            $quoteProductItem->setQuantity($quantity);
        }

        if (null !== $unitCode) {
            $quoteProductItem->setProductUnit($this->getEntity('OroB2B\Bundle\ProductBundle\Entity\ProductUnit', $unitCode, 'code'));
        }

        if (null !== $price) {
            $quoteProductItem->setPrice($price);
        }

        return $quoteProductItem;
    }
}
