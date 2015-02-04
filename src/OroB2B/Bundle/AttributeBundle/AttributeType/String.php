<?php

namespace OroB2B\Bundle\AttributeBundle\AttributeType;

use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Alphanumeric;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Decimal;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Email;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Integer as IntegerConstraint;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Letters;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\Url;
use OroB2B\Bundle\AttributeBundle\Validator\Constraints\UrlSafe;

class String extends AbstractAttributeType
{
    const NAME = 'string';
    protected $dataTypeField = 'string';

    /**
     * {@inheritdoc}
     */
    public function getFormParameters()
    {
        return [
          'type' => 'text'
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
    public function getOptionalConstraints()
    {
        return [
            new Letters(),
            new Alphanumeric(),
            new UrlSafe(),
            new Decimal(),
            new IntegerConstraint(),
            new Email(),
            new Url()
        ];
    }
}
