<?php

namespace OroB2B\Bundle\ShoppingListBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList;

class LoadShoppingListDemoData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getAdminUser($manager);

        $this->createShoppingList($manager, $user, 'Shopping List 1');
        $this->createShoppingList($manager, $user, 'Shopping List 2');

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param User $user
     * @param string $label
     *
     * @return ShoppingList
     */
    protected function createShoppingList(ObjectManager $manager, User $user, $label)
    {
        $shoppingList = new ShoppingList();
        $shoppingList->setOwner($user);
        $shoppingList->setOrganization($user->getOrganization());
        $shoppingList->setNotes('Some notes for ' . $label);
        $shoppingList->setLabel($label);

        $manager->persist($shoppingList);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return User
     */
    protected function getAdminUser(ObjectManager $manager)
    {
        $adminRole = $manager->getRepository('OroUserBundle:Role')
            ->findOneBy(['role' => LoadRolesData::ROLE_ADMINISTRATOR]);

        if (!$adminRole) {
            throw new \RuntimeException('Administrator role should exist.');
        }

        $adminUser = $manager->getRepository('OroUserBundle:Role')->getFirstMatchedUser($adminRole);

        if (!$adminUser) {
            throw new \RuntimeException('At least one administrator should exist.');
        }

        return $adminUser;
    }
}
