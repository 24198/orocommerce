<?php
namespace OroB2B\Bundle\ShoppingListBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroB2B\Bundle\ShoppingListBundle\Migrations\Schema\v1_0\OroB2BShoppingListBundle as OroB2BShoppingListBundle10;

class OroB2BShoppingListBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrob2BShoppingListTable($schema);
        $this->createOrob2BShoppingListLineItemTable($schema);

        /** Foreign keys generation **/
        $this->addOrob2BShoppingListForeignKeys($schema);
        $this->addOrob2BShoppingListLineItemForeignKeys($schema);
    }

    /**
     * Create orob2b_shopping_list table
     *
     * @param Schema $schema
     */
    protected function createOrob2BShoppingListTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_shopping_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'IDX_39731EC59EB185F9', []);
        $table->addIndex(['organization_id'], 'IDX_39731EC532C8A3DE', []);
        $table->addIndex(['created_at'], 'orob2b_shop_lst_created_at_idx', []);
    }

    /**
     * Create orob2b_shopping_list_line_item table
     *
     * @param Schema $schema
     */
    protected function createOrob2BShoppingListLineItemTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_shopping_list_line_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('shopping_list_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('unit_code', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'float', []);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(
            ['product_id', 'shopping_list_id', 'unit_code'],
            'orob2b_shopping_list_line_item_uidx'
        );
        $table->addIndex(['product_id'], 'IDX_3C7A5D2E4584665A', []);
        $table->addIndex(['shopping_list_id'], 'IDX_3C7A5D2E23245BF9', []);
        $table->addIndex(['unit_code'], 'IDX_3C7A5D2EFBD3D1C2', []);
    }

    /**
     * Add orob2b_shopping_list foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrob2BShoppingListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_shopping_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_shopping_list_line_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrob2BShoppingListLineItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_shopping_list_line_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_shopping_list'),
            ['shopping_list_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product_unit'),
            ['unit_code'],
            ['code'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
