<?php

namespace OroB2B\Bundle\OrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainMetadataProvider;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;


use OroB2B\Bundle\AccountBundle\Entity\AccountUser;
use OroB2B\Bundle\AccountBundle\Entity\AccountUserManager;
use OroB2B\Bundle\AccountBundle\Entity\AccountUserRole;
use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;

class LoadOrderUsers extends AbstractFixture implements FixtureInterface
{
    const USER1 = 'order-user1';
    const USER2 = 'order-user2';

    const ROLE1 = 'order-role1';
    const ROLE2 = 'order-role2';
    const ROLE3 = 'order-role3';

    const ACCOUNT1 = 'order-account1';
    const ACCOUNT2 = 'order-account2';

    const ACCOUNT1_USER1    = 'order-account1-user1@example.com';
    const ACCOUNT1_USER2    = 'order-account1-user2@example.com';
    const ACCOUNT1_USER3    = 'order-account1-user3@example.com';
    const ACCOUNT2_USER1    = 'order-account2-user1@example.com';

    /**
     * @var array
     */
    protected $roles = [
        self::ROLE1 => [
            [
                'class' => 'orob2b_order.entity.order.class',
                'acls'  => ['VIEW_BASIC'],
            ],
            [
                'class' => 'orob2b_account.entity.account_user.class',
                'acls'  => [],
            ],
        ],
        self::ROLE2 => [
            [
                'class' => 'orob2b_order.entity.order.class',
                'acls'  => ['VIEW_LOCAL'],
            ],
            [
                'class' => 'orob2b_account.entity.account_user.class',
                'acls'  => [],
            ],
        ],
        self::ROLE3 => [
            [
                'class' => 'orob2b_order.entity.order.class',
                'acls'  => ['VIEW_LOCAL'],
            ],
            [
                'class' => 'orob2b_account.entity.account_user.class',
                'acls'  => ['VIEW_LOCAL'],
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $accountUsers = [
        [
            'email'     => self::ACCOUNT2_USER1,
            'firstname' => 'User1FN',
            'lastname'  => 'User1LN',
            'password'  => self::ACCOUNT2_USER1,
            'account'   => self::ACCOUNT2,
            'role'      => self::ROLE1,
        ],
        [
            'email'     => self::ACCOUNT1_USER3,
            'firstname' => 'User3FN',
            'lastname'  => 'User3LN',
            'password'  => self::ACCOUNT1_USER3,
            'account'   => self::ACCOUNT1,
            'role'      => self::ROLE3,
        ],
        [
            'email'     => self::ACCOUNT1_USER2,
            'firstname' => 'User2FN',
            'lastname'  => 'User2LN',
            'password'  => self::ACCOUNT1_USER2,
            'account'   => self::ACCOUNT1,
            'role'      => self::ROLE2,
        ],
        [
            'email'     => self::ACCOUNT1_USER1,
            'firstname' => 'User1FN',
            'lastname'  => 'User1LN',
            'password'  => self::ACCOUNT1_USER1,
            'account'   => self::ACCOUNT1,
            'role'      => self::ROLE1,
        ],
    ];

    /**
     * @var array
     */
    protected $accounts = [
        [
            'name' => self::ACCOUNT1,
        ],
        [
            'name' => self::ACCOUNT2,
        ],
    ];

    /**
     * @var array
     */
    protected $users = [
        [
            'email'     => 'order-user1@example.com',
            'username'  => self::USER1,
            'password'  => self::USER1,
            'firstname' => 'OrderUser1FN',
            'lastname'  => 'OrderUser1LN',
        ],
        [
            'email'     => 'order-user2@example.com',
            'username'  => self::USER2,
            'password'  => self::USER2,
            'firstname' => 'OrderUser2FN',
            'lastname'  => 'OrderUser2LN',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createSimpleUsers($manager);

        $this->loadUsers($manager);
        $this->loadRoles($manager);
        $this->loadAccounts($manager);
        $this->loadAccountUsers($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    public function createSimpleUsers(ObjectManager $manager)
    {
        $this->createOrderUser('order.simple_user');
        $this->createOrderUser('order.simple_user2');

        $manager->flush();
    }

    /**
     * @param string $name
     */
    public function createOrderUser($name)
    {
        $userManager = $this->container->get('oro_user.manager');

        $user = $userManager->createUser();
        $user->setUsername($name)
            ->setPlainPassword('simple_password')
            ->setFirstName($name . 'first_name')
            ->setLastName($name . 'last_name')
            ->setEmail($name . '@example.com')
            ->setEnabled(true);

        $userManager->updateUser($user);

        $this->setReference($user->getUsername(), $user);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadUsers(ObjectManager $manager)
    {
        /* @var $userManager UserManager */
        $userManager = $this->container->get('oro_user.manager');

        $defaultUser = $this->getUser($manager);

        $businessUnit = $defaultUser->getOwner();
        $organization = $defaultUser->getOrganization();

        foreach ($this->users as $item) {
            /* @var $user User */
            $user = $userManager->createUser();

            $user
                ->setFirstName($item['firstname'])
                ->setLastName($item['lastname'])
                ->setEmail($item['email'])
                ->setBusinessUnits($defaultUser->getBusinessUnits())
                ->setOwner($businessUnit)
                ->setOrganization($organization)
                ->addOrganization($organization)
                ->setUsername($item['username'])
                ->setPlainPassword($item['password'])
                ->setEnabled(true)
            ;
            $userManager->updateUser($user);

            $this->setReference($user->getUsername(), $user);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadRoles(ObjectManager $manager)
    {
        /* @var $aclManager AclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        foreach ($this->roles as $key => $items) {
            $role = new AccountUserRole(AccountUserRole::PREFIX_ROLE . $key);
            $role->setLabel($key);

            foreach ($items as $acls) {
                $className = $this->container->getParameter($acls['class']);

                $this->setRolePermissions($aclManager, $role, $className, $acls['acls']);
            }

            $manager->persist($role);

            $this->setReference($key, $role);
        }

        $manager->flush();
        $aclManager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadAccountUsers(ObjectManager $manager)
    {
        /* @var $userManager AccountUserManager */
        $userManager = $this->container->get('orob2b_account_user.manager');

        $defaultUser = $this->getUser($manager);
        $organization = $defaultUser->getOrganization();

        foreach ($this->accountUsers as $item) {
            /* @var $accountUser AccountUser */
            $accountUser = $userManager->createUser();

            $accountUser
                ->setFirstName($item['firstname'])
                ->setLastName($item['lastname'])
                ->setEmail($item['email'])
                ->setAccount($this->getReference($item['account']))
                ->setConfirmed(true)
                ->setOrganization($organization)
                ->addOrganization($organization)
                ->addRole($this->getReference($item['role']))
                ->setSalt('')
                ->setPlainPassword($item['password'])
                ->setEnabled(true)
            ;

            $userManager->updateUser($accountUser);

            $this->setReference($item['email'], $accountUser);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function loadAccounts(ObjectManager $manager)
    {
        $defaultUser = $this->getUser($manager);
        $organization = $defaultUser->getOrganization();

        foreach ($this->accounts as $item) {
            $account = new Account();
            $account
                ->setName($item['name'])
                ->setOrganization($organization)
            ;
            $manager->persist($account);

            $this->addReference($item['name'], $account);
        }

        $manager->flush();
    }

    /**
     * @param AclManager $aclManager
     * @param AccountUserRole $role
     * @param string $className
     * @param array $allowedAcls
     */
    protected function setRolePermissions(AclManager $aclManager, AccountUserRole $role, $className, array $allowedAcls)
    {
        /* @var $chainMetadataProvider ChainMetadataProvider */
        $chainMetadataProvider = $this->container->get('oro_security.owner.metadata_provider.chain');

        if ($aclManager->isAclEnabled()) {
            $sid = $aclManager->getSid($role);

            $oid = $aclManager->getOid('entity:' . $className);
            $aclManager->getExtensionSelector()->select($oid);

            $chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);

            $builder = $aclManager->getMaskBuilder($oid);
            $mask = $builder->reset()->get();
            foreach ($allowedAcls as $acl) {
                $mask = $builder->add($acl)->get();
            }
            $aclManager->setPermission($sid, $oid, $mask);

            $chainMetadataProvider->stopProviderEmulation();
        }
    }
}
