<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use OroB2B\Bundle\AccountBundle\Entity\AccountCategoryVisibility;
use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\CatalogBundle\Entity\Category;

class AccountCategoryVisibilityTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $entity = new AccountCategoryVisibility();

        $this->assertPropertyAccessors(
            $entity,
            [
                ['id', 1],
                ['category', new Category()],
                ['account', new Account()],
            ]
        );
        $this->assertEquals(AccountCategoryVisibility::PARENT_CATEGORY, $entity->getDefault());
    }
}
