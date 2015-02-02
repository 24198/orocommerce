<?php

namespace OroB2B\Bundle\AttributeBundle\AttributeType;

class Text implements AttributeTypeInterface
{
    const NAME = 'text';
    const DATA_TYPE_FIELD = 'text';
    const FORM_TYPE = 'text';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTypeField()
    {
        return self::DATA_TYPE_FIELD;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormParameters(array $options = null)
    {
        return [
          'type'  => self::FORM_TYPE,
          'options'  => $options
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isContainHtml()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isUsedForSearch()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isUsedInFilters()
    {
        return false;
    }
}
