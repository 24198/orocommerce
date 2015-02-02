<?php

namespace OroB2B\Bundle\AttributeBundle\Migrations\Data\ORM;

use OroB2B\Bundle\AttributeBundle\AttributeType\Float;
use OroB2B\Bundle\AttributeBundle\AttributeType\String;

class LoadAttributeData extends AbstractLoadAttributeData
{
    /**
     * @var array
     */
    protected $attributes = [
        [
            'code' => 'sku',
            'type' => String::NAME,
            'localized' => true,
            'system' => true,
            'required' => true,
            'unique' => true,
            'label' => 'sku'
        ],
        [
            'code' => 'weight',
            'type' => Float::NAME,
            'system' => true,
            'required' => false,
            'unique' => false,
            'label' => 'weight',
        ],
        [
            'code' => 'title',
            'type' => String::NAME,
            'system' => true,
            'required' => false,
            'unique' => false,
            'label' => 'title'
        ]
    ];
}
