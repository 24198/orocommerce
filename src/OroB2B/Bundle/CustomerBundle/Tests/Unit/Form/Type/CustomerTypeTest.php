<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroB2B\Bundle\CustomerBundle\Form\Type\CustomerType;
use OroB2B\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\CustomerGroupSelectTypeStub;
use OroB2B\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\ParentCustomerSelectTypeStub;
use OroB2B\Bundle\PricingBundle\Tests\Unit\Form\Type\Stub\PriceListSelectTypeStub;

class CustomerTypeTest extends FormIntegrationTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractQuery
     */
    protected $query;

    /**
     * @var CustomerType
     */
    protected $formType;

    protected function setUp()
    {
        parent::setUp();

        $this->formType = new CustomerType();
    }

    protected function tearDown()
    {
        unset($this->em, $this->formType);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry $registry */
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));

        $entityType = new EntityType($registry);
        $customerGroupSelectType = new CustomerGroupSelectTypeStub();
        $parentCustomerSelectType = new ParentCustomerSelectTypeStub();
        $priceListSelectStub = new PriceListSelectTypeStub();

        return [
            new PreloadedExtension(
                [
                    $entityType->getName() => $entityType,
                    $customerGroupSelectType->getName() => $customerGroupSelectType,
                    $parentCustomerSelectType->getName() => $parentCustomerSelectType,
                    $priceListSelectStub->getName() => $priceListSelectStub
                ],
                []
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $options, $defaultData, $viewData, $submittedData, $expectedData)
    {
        $this->prepareRepository();

        $this->query->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                [
                    $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 2)
                ],
                [
                    $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 1),
                    $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2)
                ],
                [
                    $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', 1),
                    $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', 2)
                ]
            );

        $form = $this->factory->create($this->formType, $defaultData, $options);

        $formConfig = $form->getConfig();
        $this->assertNull($formConfig->getOption('data_class'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider()
    {
        return [
            'default' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 0,
                    'parent' => 1,
                    'priceList' => 1
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'priceList' => $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', 2)
                ]
            ],
            'empty parent' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 0,
                    'parent' => null,
                    'priceList' => 1
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => null,
                    'priceList' => $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', 2)
                ]
            ],
            'empty group' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => 1,
                    'priceList' => 1
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'priceList' => $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', 2)
                ]
            ],
            'empty price list' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 0,
                    'parent' => 1,
                    'priceList' => null
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'priceList' => null
                ]
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertInternalType('string', $this->formType->getName());
        $this->assertEquals('orob2b_customer_type', $this->formType->getName());
    }

    /**
     * @param string $className
     * @param int $id
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

    protected function prepareRepository()
    {
        $this->query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->atLeastOnce())
            ->method('getQuery')
            ->will($this->returnValue($this->query));

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->atLeastOnce())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $this->em->expects($this->any())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->will($this->returnValue($repo));

        $classMetaData = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetaData->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('OroB2B\Bundle\CustomerBundle\Entity\Customer'));
        $classMetaData->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(['id']));

        $this->em->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetaData));
    }
}
