<?php

namespace OroB2B\Bundle\ShoppingListBundle\Tests\Unit\Form\Type;

use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\ShoppingListBundle\Entity\LineItem;
use OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroB2B\Bundle\ShoppingListBundle\Tests\Unit\Form\Type\Stub\EntityType;

use OroB2B\Bundle\ShoppingListBundle\Manager\ShoppingListManager;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ShoppingListBundle\Form\Type\AddProductType;
use OroB2B\Bundle\ShoppingListBundle\Manager\LineItemManager;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;

class AddProductTypeTest extends FormIntegrationTestCase
{
    const DATA_CLASS = 'OroB2B\Bundle\ShoppingListBundle\Entity\LineItem';
    const PRODUCT_CLASS = 'OroB2B\Bundle\ProductBundle\Entity\Product';
    const SHOPPING_LIST_CLASS = 'OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList';

    /**
     * @var AddProductType
     */
    protected $type;

    /**
     * @var array
     */
    protected $units = [
        'item',
        'kg'
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var \PHPUnit_Framework_MockObject_MockObject|LineItemManager $lineItemManager */
        $lineItemManager = $this->getMockBuilder('OroB2B\Bundle\ShoppingListBundle\Manager\LineItemManager')
            ->disableOriginalConstructor()
            ->getMock();
        $lineItemManager->expects($this->any())
            ->method('roundProductQuantity')
            ->willReturnCallback(
                function ($product, $unit, $quantity) {
                    /** @var \PHPUnit_Framework_MockObject_MockObject|Product $product */
                    return round($quantity, $product->getUnitPrecision($unit)->getPrecision());
                }
            );

        /** @var \PHPUnit_Framework_MockObject_MockObject|ShoppingListManager $shoppingListManager */
        $shoppingListManager = $this->getMockBuilder('OroB2B\Bundle\ShoppingListBundle\Manager\ShoppingListManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new AddProductType(
            $this->getRegistry(),
            $lineItemManager,
            $shoppingListManager,
            $this->getSecurityContext()
        );

        $this->type->setDataClass(self::DATA_CLASS);
        $this->type->setShoppingListClass(self::SHOPPING_LIST_CLASS);
        $this->type->setProductClass(self::PRODUCT_CLASS);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $entityType = new EntityType(
            [
                1 => $this->getShoppingList(1, 'Shopping List 1'),
                2 => $this->getShoppingList(2, 'Shopping List 2'),
            ]
        );

        $productUnitSelection = new EntityType(
            $this->prepareProductUnitSelectionChoices(),
            ProductUnitSelectionType::NAME
        );

        return [
            new PreloadedExtension(
                [
                    $entityType->getName()         => $entityType,
                    ProductUnitSelectionType::NAME => $productUnitSelection,
                ],
                []
            )
        ];
    }

    public function testBuildForm()
    {
        $form = $this->factory->create($this->type);

        $this->assertTrue($form->has('shoppingList'));
        $this->assertTrue($form->has('quantity'));
        $this->assertTrue($form->has('unit'));
        $this->assertTrue($form->has('shoppingListLabel'));
    }

    public function testGetName()
    {
        $this->assertEquals(AddProductType::NAME, $this->type->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = new OptionsResolver();
        $this->type->setDefaultOptions($resolver);
        $resolvedOptions = $resolver->resolve();

        $this->assertEquals(self::DATA_CLASS, $resolvedOptions['data_class']);
        $this->assertEquals(['add_product'], $resolvedOptions['validation_groups']);
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param mixed $defaultData
     * @param mixed $submittedData
     * @param mixed $expectedData
     */
    public function testSubmit($defaultData, $submittedData, $expectedData)
    {
        $form = $this->factory->create($this->type, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);

        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        $product = $this->getProductEntityWithPrecision(1, 'kg', 3);

        $defaultLineItem = new LineItem();
        $defaultLineItem->setProduct($product);

        /** @var ShoppingList $expectedShoppingList */
        $expectedShoppingList = $this->getShoppingList(1, 'Shopping List 1');

        $expectedLineItem = clone $defaultLineItem;
        $expectedLineItem
            ->setQuantity(10)
            ->setUnit($product->getUnitPrecision('kg')->getUnit())
            ->setShoppingList($expectedShoppingList);

        return [
            'New line item with existing shopping list' => [
                'defaultData'   => $defaultLineItem,
                'submittedData' => [
                    'shoppingList'  => 1,
                    'quantity' => 10,
                    'unit'     => 'kg'
                ],
                'expectedData'  => $expectedLineItem
            ],
        ];
    }

    /**
     * @return ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRegistry()
    {
        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->any())
            ->method('find')
            ->willReturn($this->getProductEntityWithPrecision(1, 'kg', 3));

        /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry $registry */
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $registry->expects($this->any())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($repo);

        return $registry;
    }

    /**
     * @param integer $productId
     * @param string  $unitCode
     * @param integer $precision
     *
     * @return Product
     */
    protected function getProductEntityWithPrecision($productId, $unitCode, $precision = 0)
    {
        /** @var \OroB2B\Bundle\ProductBundle\Entity\Product $product */
        $product = $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', $productId);

        $unit = new ProductUnit();
        $unit->setCode($unitCode);

        $unitPrecision = new ProductUnitPrecision();
        $unitPrecision
            ->setPrecision($precision)
            ->setUnit($unit)
            ->setProduct($product);

        return $product->addUnitPrecision($unitPrecision);
    }

    /**
     * @param integer $id
     * @param string  $label
     *
     * @return Product
     */
    protected function getShoppingList($id, $label)
    {
        /** @var \OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList $shoppingList */
        $shoppingList = $this->getEntity('OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList', $id);
        $shoppingList->setLabel($label);

        return $shoppingList;
    }

    /**
     * @param string $className
     * @param int    $id
     *
     * @return object
     */
    protected function getEntity($className, $id)
    {
        $entity = new $className;

        $reflectionClass = new \ReflectionClass($className);
        $method = $reflectionClass->getProperty('id');
        $method->setAccessible(true);
        $method->setValue($entity, $id);

        return $entity;
    }

    /**
     * @return array
     */
    protected function prepareProductUnitSelectionChoices()
    {
        $choices = [];
        foreach ($this->units as $unitCode) {
            $unit = new ProductUnit();
            $unit->setCode($unitCode);
            $choices[$unitCode] = $unit;
        }

        return $choices;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SecurityContext
     */
    protected function getSecurityContext()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractToken $securityContext */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\AbstractToken');
        $token
            ->expects($this->any())
            ->method('getUsers')
            ->willReturn(new AccountUser());

        /** @var \PHPUnit_Framework_MockObject_MockObject|SecurityContext $securityContext */
        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $securityContext
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        return $securityContext;
    }

    /**
     * @param LineItem $lineItem
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected function getForm(LineItem $lineItem)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($lineItem));

        return $form;
    }
}
