<?php

namespace OroB2B\Bundle\PricingBundle\Builder;

use Doctrine\Bundle\DoctrineBundle\Registry;

use OroB2B\Bundle\PricingBundle\Entity\CombinedPriceList;
use OroB2B\Bundle\PricingBundle\Entity\CombinedPriceListToAccountGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToAccountGroup;
use OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListToAccountGroupRepository;
use OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListToAccountRepository;
use OroB2B\Bundle\PricingBundle\Provider\CombinedPriceListProvider;
use OroB2B\Bundle\PricingBundle\Provider\PriceListCollectionProvider;
use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

class AccountGroupCombinedPriceListsBuilder
{
    /**
     * @var PriceListCollectionProvider
     */
    protected $priceListCollectionProvider;

    /**
     * @var CombinedPriceListProvider
     */
    protected $combinedPriceListProvider;

    /**
     * @var string
     */
    protected $priceListToAccountGroupClassName;

    /**
     * @var string
     */
    protected $priceListToAccountClassName;

    /**
     * @var string
     */
    protected $combinedPriceListToAccountGroupClassName;


    /**
     * @var PriceListToAccountGroupRepository
     */
    protected $combinedPriceListToAccountGroupRepository;

    /**
     * @var PriceListToAccountGroupRepository
     */
    protected $priceListToAccountGroupRepository;

    /**
     * @var PriceListToAccountRepository
     */
    protected $priceListToAccountRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AccountCombinedPriceListsBuilder
     */
    protected $accountCombinedPriceListsBuilder;


    /**
     * @param $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Website $website
     * @param AccountGroup $accountGroup
     */
    public function build(AccountGroup $accountGroup, Website $website)
    {
        $this->updatePriceListsOnCurrentLevel($accountGroup, $website);
        $this->updatePriceListsOnChildrenLevels($accountGroup, $website);
        $this->deleteUnusedPriceLists($accountGroup, $website);
    }

    /**
     * @param Website $website
     */
    public function buildByWebsite(Website $website)
    {
        $groupsIterator = $this->getPriceListToAccountGroupRepository()
            ->getPriceListToAccountGroupByWebsiteIterator($website);
        /**
         * @var $accountGroupToPriceList PriceListToAccountGroup
         */
        foreach ($groupsIterator as $accountGroupToPriceList) {
            $this->build($accountGroupToPriceList->getAccountGroup(), $website);
        }
    }

    /**
     * @param Website $website
     * @param AccountGroup $accountGroup
     */
    protected function updatePriceListsOnCurrentLevel(AccountGroup $accountGroup, Website $website)
    {
        $collection = $this->priceListCollectionProvider->getPriceListsByAccountGroup($accountGroup, $website);
        $actualCombinedPriceList = $this->combinedPriceListProvider->getCombinedPriceList($collection);

        $relation = $this->getCombinedPriceListToAccountGroupRepository()
            ->findByPrimaryKey($actualCombinedPriceList, $accountGroup, $website);

        if (!$relation) {
            $this->connectNewPriceList($accountGroup, $actualCombinedPriceList);
        }
    }

    /**
     * @param Website $website
     * @param AccountGroup $accountGroup
     */
    protected function updatePriceListsOnChildrenLevels(AccountGroup $accountGroup, Website $website)
    {
        $this->accountCombinedPriceListsBuilder->buildByAccountGroup($accountGroup, $website);
    }

    /**
     * @param AccountGroup $accountGroup
     * @param Website $website
     */
    protected function deleteUnusedPriceLists(AccountGroup $accountGroup, Website $website)
    {

    }

    /**
     * @param AccountGroup $accountGroup
     * @param CombinedPriceList $combinedPriceList
     */
    protected function connectNewPriceList(AccountGroup $accountGroup, CombinedPriceList $combinedPriceList)
    {
        $relation = $this->getCombinedPriceListToAccountGroupRepository()->findOneBy(['accountGroup' => $accountGroup]);
        $manager = $this->registry->getManagerForClass($this->combinedPriceListToAccountGroupClassName);
        if (!$relation) {
            $relation = new CombinedPriceListToAccountGroup();
            $relation->setPriceList($combinedPriceList);
            $relation->setAccountGroup($accountGroup);
            $manager->persist($relation);
        }
        $relation->setPriceList($combinedPriceList);
        $manager->flush();
    }

    /**
     * @return PriceListToAccountGroupRepository
     */
    protected function getPriceListToAccountGroupRepository()
    {
        if (!$this->priceListToAccountGroupRepository) {
            $class = $this->priceListToAccountGroupClassName;
            $this->priceListToAccountGroupRepository = $this->registry->getManagerForClass($class)
                ->getRepository($class);
        }

        return $this->priceListToAccountGroupRepository;
    }

    /**
     * @return PriceListToAccountGroupRepository
     */
    protected function getCombinedPriceListToAccountGroupRepository()
    {
        if (!$this->combinedPriceListToAccountGroupRepository) {
            $class = $this->combinedPriceListToAccountGroupClassName;
            $this->combinedPriceListToAccountGroupRepository = $this->registry->getManagerForClass($class)
                ->getRepository($class);
        }

        return $this->combinedPriceListToAccountGroupRepository;
    }

    /**
     * @return PriceListToAccountRepository
     */
    public function getPriceListToAccountRepository()
    {
        if (!$this->priceListToAccountRepository) {
            $class = $this->priceListToAccountClassName;
            $this->priceListToAccountRepository = $this->registry->getManagerForClass($class)
                ->getRepository($class);
        }

        return $this->priceListToAccountRepository;
    }

    /**
     * @param CombinedPriceListProvider $combinedPriceListProvider
     */
    public function setCombinedPriceListProvider($combinedPriceListProvider)
    {
        $this->combinedPriceListProvider = $combinedPriceListProvider;
    }

    /**
     * @param PriceListCollectionProvider $priceListCollectionProvider
     */
    public function setPriceListCollectionProvider($priceListCollectionProvider)
    {
        $this->priceListCollectionProvider = $priceListCollectionProvider;
    }

    /**
     * @param mixed $priceListToAccountGroupClassName
     */
    public function setCombinedPriceListToAccountGroupClassName($priceListToAccountGroupClassName)
    {
        $this->priceListToAccountGroupClassName = $priceListToAccountGroupClassName;
        $this->priceListToAccountGroupRepository = null;
    }

    /**
     * @param mixed $priceListToAccountGroupClassName
     */
    public function setPriceListToAccountGroupClassName($priceListToAccountGroupClassName)
    {
        $this->priceListToAccountGroupClassName = $priceListToAccountGroupClassName;
        $this->priceListToAccountGroupRepository = null;
    }

    /**
     * @param string $priceListToAccountClassName
     */
    public function setPriceListToAccountClassName($priceListToAccountClassName)
    {
        $this->priceListToAccountClassName = $priceListToAccountClassName;
        $this->priceListToAccountRepository = null;
    }

    /**
     * @param AccountCombinedPriceListsBuilder $accountCombinedPriceListsBuilder
     */
    public function setAccountCombinedPriceListsBuilder($accountCombinedPriceListsBuilder)
    {
        $this->accountCombinedPriceListsBuilder = $accountCombinedPriceListsBuilder;
    }
}
