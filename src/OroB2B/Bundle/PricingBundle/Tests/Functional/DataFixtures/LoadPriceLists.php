<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToAccount;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToAccountGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToWebsite;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

class LoadPriceLists extends AbstractFixture implements DependentFixtureInterface
{
    const DEFAULT_PRIORITY = 10;

    /**
     * @var array
     */
    protected $data = [
        [
            'name' => 'priceList1',
            'reference' => 'price_list_1',
            'default' => false,
            'accounts' => [],
            'groups' => ['account_group.group1'],
            'websites' => ['US'],
            'currencies' => ['USD', 'EUR', 'AUD', 'CAD']
        ],
        [
            'name' => 'priceList2',
            'reference' => 'price_list_2',
            'default' => false,
            'accounts' => ['account.level_1.2'],
            'groups' => [],
            'websites' => [],
            'currencies' => ['USD']
        ],
        [
            'name' => 'priceList3',
            'reference' => 'price_list_3',
            'default' => false,
            'accounts' => ['account.orphan'],
            'groups' => [],
            'websites' => ['Canada'],
            'currencies' => ['CAD']
        ],
        [
            'name' => 'priceList4',
            'reference' => 'price_list_4',
            'default' => false,
            'accounts' => ['account.level_1.1'],
            'groups' => ['account_group.group2'],
            'websites' => [],
            'currencies' => ['GBP']
        ],
        [
            'name' => 'priceList5',
            'reference' => 'price_list_5',
            'default' => false,
            'accounts' => ['account.level_1.1.1'],
            'groups' => ['account_group.group3'],
            'websites' => [],
            'currencies' => ['GBP', 'EUR']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $now = new \DateTime();

        foreach ($this->data as $priceListData) {
            $priceList = new PriceList();

            $priceList
                ->setName($priceListData['name'])
                ->setDefault($priceListData['default'])
                ->setCurrencies(['USD'])
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            $defaultWebsite = $this->getDefaultWebsite();

            foreach ($priceListData['accounts'] as $accountReference) {
                /** @var Account $account */
                $account = $this->getReference($accountReference);

                $priceListToAccount = new PriceListToAccount();
                $priceListToAccount->setPriority(static::DEFAULT_PRIORITY);
                $priceListToAccount->setAccount($account);
                $priceListToAccount->setWebsite($defaultWebsite);
                $priceListToAccount->setPriceList($priceList);
            }

            foreach ($priceListData['groups'] as $accountGroupReference) {
                /** @var AccountGroup $accountGroup */
                $accountGroup = $this->getReference($accountGroupReference);

                $priceListToAccountGroup = new PriceListToAccountGroup();
                $priceListToAccountGroup->setPriority(static::DEFAULT_PRIORITY);
                $priceListToAccountGroup->setAccountGroup($accountGroup);
                $priceListToAccountGroup->setWebsite($defaultWebsite);
                $priceListToAccountGroup->setPriceList($priceList);
            }

            foreach ($priceListData['websites'] as $websiteReference) {
                /** @var Website $website */
                $website = $this->getReference($websiteReference);

                $priceListToWebsite = new PriceListToWebsite();
                $priceListToWebsite->setPriority(static::DEFAULT_PRIORITY);
                $priceListToWebsite->setWebsite($website);
                $priceListToWebsite->setPriceList($priceList);
            }

            foreach ($priceListData['currencies'] as $currencyCode) {
                $priceList->addCurrencyByCode($currencyCode);
            }

            $manager->persist($priceList);
            $this->setReference($priceListData['reference'], $priceList);
        }

        $manager->flush();
    }

    /**
     * @return Website
     */
    protected function getDefaultWebsite()
    {
        return $this->getReference('US');
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroB2B\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData',
            'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccounts',
            'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadGroups'
        ];
    }
}
