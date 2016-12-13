<?php

namespace Oro\Bundle\ShippingBundle\Tests\Unit\Entity;

use Oro\Bundle\ShippingBundle\Entity\ShippingRule;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Oro\Bundle\ShippingBundle\Entity\ShippingRuleMethodTypeConfig;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ShippingMethodConfigTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $properties = [
            ['id', 1],
            ['method', 'custom'],
            ['options', ['custom' => 'test']],
            ['rule', new ShippingRule()],
        ];

        $entity = new ShippingMethodConfig();

        $this->assertPropertyAccessors($entity, $properties);
        $this->assertPropertyCollection($entity, 'typeConfigs', new ShippingRuleMethodTypeConfig());
    }
}
