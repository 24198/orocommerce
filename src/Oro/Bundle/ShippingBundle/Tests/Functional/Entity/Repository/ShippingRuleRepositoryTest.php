<?php

namespace Oro\Bundle\ShippingBundle\Tests\Functional\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Oro\Bundle\ShippingBundle\Entity\ShippingRule;
use Oro\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadShippingRules;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class ShippingRuleRepositoryTest extends WebTestCase
{
    /**
     * @var ShippingMethodsConfigsRuleRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures([
            LoadShippingRules::class,
        ]);

        $this->em = static::getContainer()->get('doctrine')->getManagerForClass('OroShippingBundle:ShippingRule');
        $this->repository = $this->em->getRepository('OroShippingBundle:ShippingRule');
    }

    /**
     * @dataProvider getOrderedRulesByCurrencyDataProvider
     *
     * @param string $currency
     * @param string $country
     * @param array $expectedRules
     */
    public function testGetOrderedRulesByCurrency($currency, $country, array $expectedRules)
    {
        /** @var ShippingRule[]|array $expectedShippingRule */
        $expectedShippingRules = $this->getEntitiesByReferences($expectedRules);
        /** @var ShippingRule $expectedShippingRule */
        $expectedShippingRule = $expectedShippingRules[0];
        $shippingRules = $this->repository->getEnabledOrderedRulesByCurrencyAndCountry(
            $currency,
            $country
        );

        static::assertNotFalse(strpos(serialize($shippingRules), $expectedShippingRule->getName()));
        static::assertNotFalse(strpos(serialize($shippingRules), $expectedShippingRule->getCurrency()));
        static::assertNotFalse(strpos(serialize($shippingRules), $expectedShippingRule->getConditions()));
    }

    public function testGetRulesWithoutShippingMethods()
    {
        $rulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods();
        $enabledRulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods(true);

        static::assertCount(2, $rulesWithoutShippingMethods);
        static::assertCount(1, $enabledRulesWithoutShippingMethods);
    }

    public function testDisableRulesWithoutShippingMethods()
    {
        $this->repository->disableRulesWithoutShippingMethods();

        $rulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods();
        $enabledRulesWithoutShippingMethods = $this->repository->getRulesWithoutShippingMethods(true);

        static::assertCount(2, $rulesWithoutShippingMethods);
        static::assertCount(0, $enabledRulesWithoutShippingMethods);
    }

    /**
     * @return array
     */
    public function getOrderedRulesByCurrencyDataProvider()
    {
        return [
            [
                'currency' => 'USD',
                'country' => 'US',
                'expectedRules' => [
                    'shipping_rule.8',
                    'shipping_rule.7',
                ]
            ],
            [
                'currency' => 'EUR',
                'country' => 'US',
                'expectedRules' => [
                    'shipping_rule.1',
                    'shipping_rule.2',
                    'shipping_rule.4',
                    'shipping_rule.5',
                ]
            ],
        ];
    }

    /**
     * @param array $rules
     * @return array
     */
    protected function getEntitiesByReferences(array $rules)
    {
        return array_map(function ($ruleReference) {
            return $this->getReference($ruleReference);
        }, $rules);
    }

    public function testGetLastUpdateAt()
    {
        $updatedAt = $this->repository->getLastUpdateAt();

        $shippingRule = $this->repository->findOneBy([]);
        $shippingRule->setPriority($shippingRule->getPriority() + 1);

        $this->em->persist($shippingRule);
        $this->em->flush($shippingRule);

        $newUpdatedAt = $this->repository->getLastUpdateAt();
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $newUpdatedAt->getTimestamp());
    }
}
