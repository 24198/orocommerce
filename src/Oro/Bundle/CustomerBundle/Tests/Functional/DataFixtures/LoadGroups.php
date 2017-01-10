<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

class LoadGroups extends AbstractFixture
{
    const GROUP1 = 'account_group.group1';
    const GROUP2 = 'account_group.group2';
    const GROUP3 = 'account_group.group3';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createGroup($manager, self::GROUP1);
        $this->createGroup($manager, self::GROUP2);
        $this->createGroup($manager, self::GROUP3);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $name
     * @return CustomerGroup
     */
    protected function createGroup(ObjectManager $manager, $name)
    {
        $group = new CustomerGroup();
        $group->setName($name);
        $manager->persist($group);
        $this->addReference($name, $group);

        return $group;
    }
}
