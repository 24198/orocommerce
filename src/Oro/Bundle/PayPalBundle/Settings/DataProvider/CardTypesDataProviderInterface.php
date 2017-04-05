<?php

namespace Oro\Bundle\PayPalBundle\Settings\DataProvider;

interface CardTypesDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getCardTypes();

    /**
     * @return string[]
     */
    public function getDefaultCardTypes();
}
