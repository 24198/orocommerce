<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Entity\AccountUser;

class LoadAccountUserData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_NAME = 'Grzegorz';
    const LAST_NAME = 'Brzeczyszczykiewicz';
    const EMAIL = 'grzegorz.brzeczyszczykiewicz@example.com';
    const PASSWORD = 'test';

    /**
     * @var array
     */
    protected $users = [
        [
            'first_name' => self::FIRST_NAME,
            'last_name' => self::LAST_NAME,
            'email' => self::EMAIL,
            'enabled' => true,
            'password' => self::PASSWORD,
            'account' => 'account.level_1'
        ],
        [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'other.user@test.com',
            'enabled' => true,
            'password' => 'pass',
            'account' => 'account.level_1'
        ],
        [
            'first_name' => 'FirstName',
            'last_name' => 'LastName',
            'email' => 'second_account.user@test.com',
            'enabled' => true,
            'password' => 'pass',
            'account' => 'account.level_1.1'
        ]
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->users as $user) {
            /** @var Account $account */
            $account = $this->getReference($user['account']);

            $entity = new AccountUser();
            $entity
                ->setAccount($account)
                ->setFirstName($user['first_name'])
                ->setLastName($user['last_name'])
                ->setEmail($user['email'])
                ->setEnabled($user['enabled'])
                ->setPassword($user['password']);

            $this->setReference($entity->getEmail(), $entity);

            $manager->persist($entity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccounts'
        ];
    }
}
