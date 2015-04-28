<?php

namespace OroB2B\Bundle\CustomerAdminBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroB2BCustomerAdminBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroB2BCustomerTable($schema);
        $this->createOroB2BCustomerGroupTable($schema);

        /** Foreign keys generation **/
        $this->addOroB2BCustomerForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createOroB2BCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_customer');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['name'], 'orob2b_customer_name_idx', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createOroB2BCustomerGroupTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_customer_group');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['name'], 'orob2b_customer_group_name_idx', []);
    }

    /**
     * @param Schema $schema
     */
    protected function addOroB2BCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_customer_group'),
            ['group_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
