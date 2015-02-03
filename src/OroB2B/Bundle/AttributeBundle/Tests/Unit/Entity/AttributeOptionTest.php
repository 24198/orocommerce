<?php

namespace OroB2B\Bundle\WebsiteBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCase;
use OroB2B\Bundle\AttributeBundle\Entity\Attribute;
use OroB2B\Bundle\AttributeBundle\Entity\AttributeOption;
use OroB2B\Bundle\WebsiteBundle\Entity\Locale;

class AttributeOptionTest extends EntityTestCase
{

    public function testProperties()
    {
        $locale = new Locale();
        $locale->setCode('es_MX');

        $attribute = new Attribute();
        $attribute->setType('select');

        $properties = [
            ['id', 1],
            ['value', 'test'],
            ['order', 5],
            ['fallback', 'website'],
            ['locale', $locale, false],
            ['attribute', $attribute, false],
        ];

        $this->assertPropertyAccessors(new AttributeOption(), $properties);
    }
}
