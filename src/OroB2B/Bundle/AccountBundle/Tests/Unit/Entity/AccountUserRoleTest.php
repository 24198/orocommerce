<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Entity\AccountUserRole;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

class AccountUserRoleTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testRole()
    {
        $name = 'test role#$%';
        $role = new AccountUserRole();

        $this->assertEmpty($role->getId());
        $this->assertEmpty($role->getLabel());
        $this->assertEmpty($role->getRole());

        $role->setLabel($name);
        $this->assertEquals($name, $role->getLabel());

        $this->assertEquals(AccountUserRole::PREFIX_ROLE, $role->getPrefix());

        $role->setRole($name);
        $this->assertStringStartsWith(AccountUserRole::PREFIX_ROLE . 'TEST_ROLE_', $role->getRole());

        $this->assertEquals($name, (string)$role);
    }

    /**
     * Test AccountUserRole relations
     */
    public function testRelations()
    {
        static::assertPropertyCollections(new AccountUserRole(), [
            ['websites', new Website()],
        ]);

        static::assertPropertyAccessors(new AccountUserRole(), [
            ['account', new Account()],
            ['organization', new Organization()]
        ]);
    }

    public function testNotEmptyRole()
    {
        $name = 'another test role';
        $role = new AccountUserRole($name);
        $this->assertEquals(AccountUserRole::PREFIX_ROLE . 'ANOTHER_TEST_ROLE', $role->getRole());
    }
}
