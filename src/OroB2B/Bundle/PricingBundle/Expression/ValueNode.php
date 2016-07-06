<?php

namespace OroB2B\Bundle\PricingBundle\Expression;

class ValueNode implements NodeInterface
{
    /**
     * @var int|float|string
     */
    protected $value;

    /**
     * @param int|float|string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return float|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodes()
    {
        return [$this];
    }
}
