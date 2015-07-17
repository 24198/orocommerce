<?php

namespace OroB2B\Bundle\PricingBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter;

use OroB2B\Bundle\PricingBundle\Form\Type\Filter\ProductPriceFilterType;

class ProductPriceFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return ProductPriceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        return parent::apply($ds, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();
        $metadata['unitChoices'] = $this->getForm()->createView()['unit']->vars['choices'];

        return $metadata;
    }
}
