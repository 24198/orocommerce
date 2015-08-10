<?php

namespace OroB2B\Bundle\OrderBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

use OroB2B\Bundle\OrderBundle\Provider\OrderAddressSecurityProvider;
use OroB2B\Bundle\OrderBundle\Form\Type\OrderType;

class OrderTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderType */
    protected $type;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderAddressSecurityProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('OroB2B\Bundle\OrderBundle\Provider\OrderAddressSecurityProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new OrderType($this->provider);
    }

    public function testConfigureOptions()
    {
        /* @var $resolver \PHPUnit_Framework_MockObject_MockObject|OptionsResolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => 'Order',
                ]
            );

        $this->type->setDataClass('Order');
        $this->type->configureOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orob2b_order_type', $this->type->getName());
    }
}
