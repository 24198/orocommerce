<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Form\Type;

use OroB2B\Bundle\CatalogBundle\Form\Type\CategoryTreeType;
use OroB2B\Bundle\PricingBundle\Form\Type\ProductPriceCollectionType;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductType;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitPrecisionCollectionType;

class ProductTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new ProductType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->at(0))
            ->method('add')
            ->with('sku', 'text')
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'category',
                CategoryTreeType::NAME,
                ['required' => false, 'label' => 'orob2b.product.category.label']
            )
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with(
                'unitPrecisions',
                ProductUnitPrecisionCollectionType::NAME,
                [
                    'label' => 'orob2b.product.unit_precisions.label',
                    'tooltip' => 'orob2b.product.form.tooltip.unit_precision',
                    'required' => false
                ]
            )
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with(
                'prices',
                ProductPriceCollectionType::NAME,
                [
                    'label' => 'orob2b.product.prices.label',
                    'required' => false
                ]
            )
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => 'OroB2B\Bundle\ProductBundle\Entity\Product',
                    'intention' => 'product',
                    'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"'
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orob2b_product', $this->type->getName());
    }
}
