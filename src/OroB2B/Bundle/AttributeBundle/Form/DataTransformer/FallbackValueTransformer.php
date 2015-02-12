<?php

namespace OroB2B\Bundle\AttributeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

use OroB2B\Bundle\AttributeBundle\Model\FallbackType;

class FallbackValueTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $result = ['value' => null, 'fallback' => null];

        if ($value instanceof FallbackType) {
            $result['fallback'] = $value->getType();
        } else {
            $result['value'] = $value;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!empty($value['fallback'])) {
            return new FallbackType($value['fallback']);
        } elseif (isset($value['value'])) {
            return $value['value'];
        }

        return null;
    }
}
