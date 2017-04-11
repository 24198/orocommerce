<?php

namespace Oro\Bundle\FlatRateShippingBundle\Method;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FlatRateShippingBundle\Builder\FlatRateMethodFromChannelBuilder;
use Oro\Bundle\FlatRateShippingBundle\Integration\FlatRateChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Oro\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

/**
 * @deprecated since 1.2, will be removed in 1.3.
 * Use Oro\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider instead.
 */
class FlatRateMethodProvider implements ShippingMethodProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var FlatRateMethodFromChannelBuilder
     */
    private $methodBuilder;

    /**
     * @var ShippingMethodInterface[]|array
     */
    protected $methods;

    /**
     * @param DoctrineHelper                   $doctrineHelper
     * @param FlatRateMethodFromChannelBuilder $methodBuilder
     */
    public function __construct(DoctrineHelper $doctrineHelper, FlatRateMethodFromChannelBuilder $methodBuilder)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->methodBuilder = $methodBuilder;
        $this->methods = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethods()
    {
        if (!$this->methods) {
            $channels = $this->getFlatRateChannels();

            foreach ($channels as $channel) {
                $this->addFlatRateMethod($channel);
            }
        }

        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethod($name)
    {
        if ($this->hasShippingMethod($name)) {
            return $this->getShippingMethods()[$name];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasShippingMethod($name)
    {
        return array_key_exists($name, $this->getShippingMethods());
    }

    /**
     * @param Channel $channel
     */
    private function addFlatRateMethod(Channel $channel)
    {
        $method = $this->methodBuilder->build($channel);
        $this->methods[$method->getIdentifier()] = $method;
    }

    /**
     * @return array|Channel[]
     */
    private function getFlatRateChannels()
    {
        return $this->getRepository()->findByType(FlatRateChannelType::TYPE);
    }

    /**
     * @return ChannelRepository|\Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->doctrineHelper->getEntityRepository('OroIntegrationBundle:Channel');
    }
}
