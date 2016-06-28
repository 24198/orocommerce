<?php

namespace OroB2B\Bundle\PricingBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddPriceRules implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroB2BPriceRuleTable($schema);
        $this->createOroB2BPriceRuleChTriggerTable($schema);
        $this->createOroB2BPriceRuleLexemeTable($schema);

        /** Foreign keys generation **/
        $this->addOroB2BPriceRuleForeignKeys($schema);
        $this->addOroB2BPriceRuleChTriggerForeignKeys($schema);
        $this->addOroB2BPriceRuleLexemeForeignKeys($schema);

        $this->updateProductPriceTable($schema);
    }

    /**
     * Create orob2b_price_rule table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceRuleTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_rule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_unit_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('price_list_id', 'integer', []);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 3]);
        $table->addColumn('quantity', 'float', ['notnull' => false]);
        $table->addColumn('rule_condition', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('rule', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('priority', 'integer', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orob2b_price_rule_ch_trigger table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceRuleChTriggerTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_rule_ch_trigger');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('price_list_id', 'integer', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orob2b_price_rule_lexeme table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceRuleLexemeTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_rule_lexeme');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('price_rule_id', 'integer', []);
        $table->addColumn('class_name', 'string', ['length' => 255]);
        $table->addColumn('field_name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add orob2b_price_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BPriceRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_price_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product_unit'),
            ['product_unit_id'],
            ['code'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_list'),
            ['price_list_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_price_rule_ch_trigger foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BPriceRuleChTriggerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_price_rule_ch_trigger');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_list'),
            ['price_list_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_price_rule_lexeme foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BPriceRuleLexemeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_price_rule_lexeme');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_rule'),
            ['price_rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function updateProductPriceTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_product');
        $table->addColumn('price_rule_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_rule'),
            ['price_rule_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
