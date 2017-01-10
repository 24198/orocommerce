<?php
namespace Oro\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\PricingBundle\Entity\BasePriceListRelation;
use Oro\Bundle\PricingBundle\Entity\PriceListAccountFallback;
use Oro\Bundle\PricingBundle\Entity\PriceListAccountGroupFallback;
use Oro\Bundle\PricingBundle\Entity\PriceListWebsiteFallback;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceListToAccountGroupRepository;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceListToAccountRepository;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceListToWebsiteRepository;
use Oro\Bundle\PricingBundle\SystemConfig\PriceListConfig;
use Oro\Bundle\PricingBundle\SystemConfig\PriceListConfigConverter;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class PriceListCollectionProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var PriceListConfigConverter
     */
    protected $configConverter;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigManager $configManager
     * @param PriceListConfigConverter $configConverter
     */
    public function __construct(
        ManagerRegistry $registry,
        ConfigManager $configManager,
        PriceListConfigConverter $configConverter
    ) {
        $this->registry = $registry;
        $this->configManager = $configManager;
        $this->configConverter = $configConverter;
    }

    /**
     * @return PriceListSequenceMember[]
     */
    public function getPriceListsByConfig()
    {
        /** @var PriceListConfig[] $priceListsConfig */
        $priceListsConfig = $this->configConverter->convertFromSaved(
            $this->configManager->get('oro_pricing.default_price_lists')
        );
        $activeRelations = [];
        foreach ($priceListsConfig as $priceList) {
            if ($priceList->getPriceList()->isActive()) {
                $activeRelations[] = $priceList;
            }
        }
        return $this->getPriceListSequenceMembers($activeRelations);
    }

    /**
     * @param Website $website
     * @return PriceListSequenceMember[]
     */
    public function getPriceListsByWebsite(Website $website)
    {
        /** @var PriceListToWebsiteRepository $repo */
        $repo = $this->getRepository('OroPricingBundle:PriceListToWebsite');
        $priceListCollection = $this->getPriceListSequenceMembers(
            $repo->getPriceLists($website)
        );
        $fallbackEntity = $this->registry
            ->getRepository('OroPricingBundle:PriceListWebsiteFallback')
            ->findOneBy(['website' => $website]);
        if (!$fallbackEntity || $fallbackEntity->getFallback() === PriceListWebsiteFallback::CONFIG) {
            return array_merge($priceListCollection, $this->getPriceListsByConfig());
        }
        return $priceListCollection;
    }

    /**
     * @param CustomerGroup $accountGroup
     * @param Website $website
     * @return PriceListSequenceMember[]
     */
    public function getPriceListsByAccountGroup(CustomerGroup $accountGroup, Website $website)
    {
        /** @var PriceListToAccountGroupRepository $repo */
        $repo = $this->getRepository('OroPricingBundle:PriceListToAccountGroup');
        $priceListCollection = $this->getPriceListSequenceMembers(
            $repo->getPriceLists($accountGroup, $website)
        );
        $fallbackEntity = $this->registry
            ->getRepository('OroPricingBundle:PriceListAccountGroupFallback')
            ->findOneBy(['accountGroup' => $accountGroup, 'website' => $website]);
        if (!$fallbackEntity || $fallbackEntity->getFallback() === PriceListAccountGroupFallback::WEBSITE) {
            return array_merge($priceListCollection, $this->getPriceListsByWebsite($website));
        }
        return $priceListCollection;
    }

    /**
     * @param Customer $account
     * @param Website $website
     * @return PriceListSequenceMember[]
     */
    public function getPriceListsByAccount(Customer $account, Website $website)
    {
        /** @var PriceListToAccountRepository $repo */
        $repo = $this->getRepository('OroPricingBundle:PriceListToAccount');
        $priceListCollection = $this->getPriceListSequenceMembers(
            $repo->getPriceLists($account, $website)
        );

        $fallbackEntity = $this->registry
            ->getRepository('OroPricingBundle:PriceListAccountFallback')
            ->findOneBy(['account' => $account, 'website' => $website]);

        if ($this->isFallbackToCurrentAccountOnly($fallbackEntity)) {
            $priceLists = $priceListCollection;
        } elseif ($account->getGroup() && $this->isFallbackToAccountGroup($fallbackEntity)) {
            $priceLists = array_merge(
                $priceListCollection,
                $this->getPriceListsByAccountGroup($account->getGroup(), $website)
            );
        } else {
            $priceLists = array_merge($priceListCollection, $this->getPriceListsByWebsite($website));
        }

        return $priceLists;
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    public function getRepository($className)
    {
        return $this->registry
            ->getManagerForClass($className)
            ->getRepository($className);
    }

    /**
     * @param BasePriceListRelation[]|PriceListConfig[] $priceListsRelations
     * @return PriceListSequenceMember[]
     */
    protected function getPriceListSequenceMembers($priceListsRelations)
    {
        $priceListCollection = [];
        foreach ($priceListsRelations as $priceListsRelation) {
            $priceListCollection[] = new PriceListSequenceMember(
                $priceListsRelation->getPriceList(),
                $priceListsRelation->isMergeAllowed()
            );
        }
        return $priceListCollection;
    }

    /**
     * @param PriceListAccountFallback|null $fallbackEntity
     * @return bool
     */
    protected function isFallbackToCurrentAccountOnly(PriceListAccountFallback $fallbackEntity = null)
    {
        return $fallbackEntity && $fallbackEntity->getFallback() === PriceListAccountFallback::CURRENT_ACCOUNT_ONLY;
    }

    /**
     * @param PriceListAccountFallback|null $fallbackEntity
     * @return bool
     */
    protected function isFallbackToAccountGroup(PriceListAccountFallback $fallbackEntity = null)
    {
        return !$fallbackEntity || $fallbackEntity->getFallback() === PriceListAccountFallback::ACCOUNT_GROUP;
    }
}
