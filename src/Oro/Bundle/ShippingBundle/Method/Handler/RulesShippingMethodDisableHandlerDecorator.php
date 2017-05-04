<?php

namespace Oro\Bundle\ShippingBundle\Method\Handler;

use Oro\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\ShippingBundle\Method\ShippingMethodRegistry;

class RulesShippingMethodDisableHandlerDecorator implements ShippingMethodDisableHandlerInterface
{
    /**
     * @var ShippingMethodDisableHandlerInterface
     */
    private $handler;

    /**
     * @var ShippingMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @var ShippingMethodRegistry
     */
    private $methodRegistry;

    /**
     * @param ShippingMethodDisableHandlerInterface $handler
     * @param ShippingMethodsConfigsRuleRepository  $repository
     * @param ShippingMethodRegistry                $methodRegistry
     */
    public function __construct(
        ShippingMethodDisableHandlerInterface $handler,
        ShippingMethodsConfigsRuleRepository $repository,
        ShippingMethodRegistry $methodRegistry
    ) {
        $this->handler = $handler;
        $this->repository = $repository;
        $this->methodRegistry = $methodRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function handleMethodDisable($methodId)
    {
        $this->handler->handleMethodDisable($methodId);
        $shippingMethodsConfigsRule = $this->repository->getEnabledRulesByMethod($methodId);
        foreach ($shippingMethodsConfigsRule as $configRule) {
            if (!$this->configHasEnabledMethod($configRule, $methodId)) {
                $rule = $configRule->getRule();
                $rule->setEnabled(false);
            }
        }
    }

    /**
     * @param ShippingMethodsConfigsRule $configRule
     * @param string                     $disabledMethodId
     *
     * @return bool
     */
    private function configHasEnabledMethod(ShippingMethodsConfigsRule $configRule, $disabledMethodId)
    {
        $methodConfigs = $configRule->getMethodConfigs();
        foreach ($methodConfigs as $methodConfig) {
            $methodId = $methodConfig->getMethod();
            if ($methodId !== $disabledMethodId) {
                $method = $this->methodRegistry->getShippingMethod($methodId);
                if ($method->isEnabled()) {
                    return true;
                }
            }
        }

        return false;
    }
}
