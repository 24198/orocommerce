<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Oro\Bundle\CustomerBundle\Entity\Customer;

class LoadAccountDemoData extends AbstractEntityReferenceFixture implements DependentFixtureInterface
{
    const ACCOUNT_REFERENCE_PREFIX = 'account_demo_data';

    /** @var array */
    protected $accounts = [
        'Company A' => [
            'group' => 'All Customers',
            'subsidiaries' => [
                'Company A - East Division' => [
                    'group' => 'All Customers',
                ],
                'Company A - West Division' => [
                    'group' => 'All Customers',
                ],
            ],
        ],
        'Wholesaler B' => [
            'group' => 'Wholesale Accounts',
        ],
        'Partner C' => [
            'group' => 'Partners',
        ],
        'Account G' => [
            'group' => 'All Customers',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAccountInternalRatingDemoData::class,
            LoadAccountGroupDemoData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $internalRatings = $this->getObjectReferencesByIds(
            $manager,
            ExtendHelper::buildEnumValueClassName(Customer::INTERNAL_RATING_CODE),
            LoadAccountInternalRatingDemoData::getDataKeys()
        );

        /** @var \Oro\Bundle\UserBundle\Entity\User $accountOwner */
        $accountOwner = $manager->getRepository('OroUserBundle:User')->findOneBy([]);

        foreach ($this->accounts as $accountName => $accountData) {
            /** @var CustomerGroup $accountGroup */
            $accountGroup = $this->getReference(
                LoadAccountGroupDemoData::ACCOUNT_GROUP_REFERENCE_PREFIX . $accountData['group']
            );

            $account = new Customer();
            $account
                ->setName($accountName)
                ->setGroup($accountGroup)
                ->setParent(null)
                ->setOrganization($accountOwner->getOrganization())
                ->setOwner($accountOwner)
                ->setInternalRating($internalRatings[array_rand($internalRatings)]);

            $manager->persist($account);
            $this->addReference(static::ACCOUNT_REFERENCE_PREFIX . $account->getName(), $account);

            if (isset($accountData['subsidiaries'])) {
                foreach ($accountData['subsidiaries'] as $subsidiaryName => $subsidiaryData) {
                    /** @var CustomerGroup $subsidiaryGroup */
                    $subsidiaryGroup = $this->getReference(
                        LoadAccountGroupDemoData::ACCOUNT_GROUP_REFERENCE_PREFIX . $subsidiaryData['group']
                    );
                    $subsidiary = new Customer();
                    $subsidiary
                        ->setName($subsidiaryName)
                        ->setGroup($subsidiaryGroup)
                        ->setParent($account)
                        ->setOrganization($accountOwner->getOrganization())
                        ->setOwner($accountOwner)
                        ->setInternalRating($internalRatings[array_rand($internalRatings)]);

                    $manager->persist($subsidiary);
                    $this->addReference(static::ACCOUNT_REFERENCE_PREFIX . $subsidiary->getName(), $subsidiary);
                }
            }
        }

        $manager->flush();
    }
}
