<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductRowType;
use OroB2B\Bundle\ProductBundle\Model\DataStorageAwareProcessor;
use OroB2B\Bundle\ProductBundle\Storage\ProductDataStorage;
use OroB2B\Bundle\ProductBundle\Tests\Unit\Form\Extension\Stub\PostQuickAddTypeExtensionStub;

class AbstractPostQuickAddTypeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ProductDataStorage */
    protected $storage;

    /** @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Request */
    protected $request;

    /** @var PostQuickAddTypeExtensionStub */
    protected $extension;

    /** @var string */
    protected $productClass = 'stdClass';

    /** @var string */
    protected $dataClass = '\stdClass';

    /** @var object */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->storage = $this->getMockBuilder('OroB2B\Bundle\ProductBundle\Storage\ProductDataStorage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new PostQuickAddTypeExtensionStub(
            $this->storage,
            $this->doctrineHelper,
            $this->productClass
        );
        $this->extension->setRequest($this->request);
        $this->extension->setDataClass($this->dataClass);

        $this->entity = new \stdClass();
    }

    protected function tearDown()
    {
        unset($this->storage, $this->registry, $this->productClass, $this->extension, $this->dataClass);
    }

    public function testBuildFormNoRequestParameter()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|FormBuilderInterface $builder */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $this->assertRequestGetCalled(false);

        $this->storage->expects($this->never())
            ->method($this->anything());

        $this->extension->buildForm($builder, ['data' => $this->entity]);
    }

    public function testBuildFormExistingEntity()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|FormBuilderInterface $builder */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $this->doctrineHelper->expects($this->any())->method('getSingleEntityIdentifier')->willReturn(1);

        $this->assertRequestGetCalled();

        $this->storage->expects($this->never())
            ->method($this->anything());

        $this->extension->buildForm($builder, ['data' => $this->entity]);
    }

    public function testBuildFormNoData()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|FormBuilderInterface $builder */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $this->doctrineHelper->expects($this->once())->method('getSingleEntityIdentifier')->willReturn(null);
        $this->doctrineHelper->expects($this->never())->method('getEntityRepository');

        $this->assertRequestGetCalled();
        $this->assertStorageCalled([]);

        $this->extension->buildForm($builder, ['data' => $this->entity]);
    }

    public function testBuild()
    {
        $this->assertFalse($this->extension->isAddProductToEntityCalled());

        $sku = 'TEST';
        $product = $this->getProductEntity($sku);
        $data = [[ProductRowType::PRODUCT_SKU_FIELD_NAME => $sku, ProductRowType::PRODUCT_QUANTITY_FIELD_NAME => 3]];

        $this->assertRequestGetCalled();
        $this->assertStorageCalled($data);
        $this->assertProductRepositoryCalled($product);

        /** @var \PHPUnit_Framework_MockObject_MockObject|FormBuilderInterface $builder */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $this->extension->buildForm($builder, ['data' => $this->entity]);

        $this->assertTrue($this->extension->isAddProductToEntityCalled());
    }

    /**
     * @param string $sku
     * @param ProductUnit|null $productUnit
     * @return Product
     */
    protected function getProductEntity($sku, ProductUnit $productUnit = null)
    {
        $product = new Product();
        $product->setSku($sku);

        if ($productUnit) {
            $productUnitPrecision = new ProductUnitPrecision();
            $productUnitPrecision->setUnit($productUnit);

            $product->addUnitPrecision($productUnitPrecision);
        }

        return $product;
    }

    /**
     * @param bool $result
     */
    protected function assertRequestGetCalled($result = true)
    {
        $this->request->expects($this->once())
            ->method('get')
            ->with(DataStorageAwareProcessor::QUICK_ADD_PARAM)
            ->willReturn($result);
    }

    /**
     * @param array $data
     */
    protected function assertStorageCalled(array $data)
    {
        $this->storage->expects($this->once())
            ->method('get')
            ->willReturn($data);
        $this->storage->expects($this->once())
            ->method('remove');
    }

    /**
     * @param Product $product
     */
    protected function assertProductRepositoryCalled(Product $product)
    {
        $repo = $this->getMockBuilder('OroB2B\Bundle\ProductBundle\Entity\Repository\ProductRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findOneBySku')
            ->with($product->getSku())
            ->willReturn($product);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with($this->productClass)
            ->willReturn($repo);
    }
}
