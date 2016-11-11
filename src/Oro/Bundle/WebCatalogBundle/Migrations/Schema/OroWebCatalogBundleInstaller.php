<?php

namespace Oro\Bundle\WebCatalogBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroWebCatalogBundleInstaller implements
    Installation,
    NoteExtensionAwareInterface
{
    /**
     * @var NoteExtension
     */
    protected $noteExtension;

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

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
        $this->createOroWebCatalogTable($schema);
        $this->createOroContentVariantTable($schema);
        $this->createOroContentNodeTable($schema);
        $this->createOroContentNodeSlugPrototypeTable($schema);
        $this->createOroContentNodeTitleTable($schema);
        $this->createOroContentNodeSlugTable($schema);
        $this->createOroWebCatalogNodeScopeTable($schema);
        $this->createOroWebCatalogVariantScopeTable($schema);

        /** Foreign keys generation **/
        $this->addOroWebCatalogForeignKeys($schema);
        $this->addOroContentNodeForeignKeys($schema);
        $this->addOroContentNodeSlugPrototypeForeignKeys($schema);
        $this->addOroContentNodeTitleForeignKeys($schema);
        $this->addOroContentNodeSlugForeignKeys($schema);
        $this->addOroContentVariantForeignKeys($schema);
        $this->addOroWebCatalogNodeScopeForeignKeys($schema);
        $this->addOroWebCatalogVariantScopeForeignKeys($schema);
    }

    /**
     * Create oro_web_catalog table
     *
     * @param Schema $schema
     */
    protected function createOroWebCatalogTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_unit_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $this->noteExtension->addNoteAssociation($schema, $table->getName());
    }

    /**
     * Create oro_web_catalog_variant table
     *
     * @param Schema $schema
     */
    protected function createOroContentVariantTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_variant');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('node_id', 'integer', ['notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('system_page_route', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['node_id']);
    }

    /**
     * Create oro_web_catalog_content_node table
     *
     * @param Schema $schema
     */
    protected function createOroContentNodeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_content_node');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('web_catalog_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('materialized_path', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('tree_left', 'integer', []);
        $table->addColumn('tree_level', 'integer', []);
        $table->addColumn('tree_right', 'integer', []);
        $table->addColumn('tree_root', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $this->noteExtension->addNoteAssociation($schema, $table->getName());
    }

    /**
     * Create oro_web_catalog_node_slug_prot table
     *
     * @param Schema $schema
     */
    protected function createOroContentNodeSlugPrototypeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_node_slug_prot');
        $table->addColumn('node_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['node_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_web_catalog_node_title table
     *
     * @param Schema $schema
     */
    protected function createOroContentNodeTitleTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_node_title');
        $table->addColumn('node_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['node_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_web_catalog_node_slug table
     *
     * @param Schema $schema
     */
    protected function createOroContentNodeSlugTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_node_slug');
        $table->addColumn('node_id', 'integer', []);
        $table->addColumn('slug_id', 'integer', []);
        $table->setPrimaryKey(['node_id', 'slug_id']);
        $table->addUniqueIndex(['slug_id']);
    }

    /**
     * Create oro_web_catalog_node_scope table
     *
     * @param Schema $schema
     */
    protected function createOroWebCatalogNodeScopeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_node_scope');
        $table->addColumn('node_id', 'integer', []);
        $table->addColumn('scope_id', 'integer', []);
        $table->setPrimaryKey(['node_id', 'scope_id']);
    }

    /**
     * Create oro_web_catalog_variant_scope table
     *
     * @param Schema $schema
     */
    protected function createOroWebCatalogVariantScopeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_web_catalog_variant_scope');
        $table->addColumn('variant_id', 'integer', []);
        $table->addColumn('scope_id', 'integer', []);
        $table->setPrimaryKey(['variant_id', 'scope_id']);
    }

    /**
     * Add oro_web_catalog foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroWebCatalogForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_business_unit'),
            ['business_unit_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_web_catalog_content_node foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroContentNodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_content_node');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['parent_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog'),
            ['web_catalog_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_web_catalog_node_slug_prot foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroContentNodeSlugPrototypeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_node_slug_prot');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['node_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_web_catalog_node_title foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroContentNodeTitleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_node_title');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['node_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_web_catalog_node_slug foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroContentNodeSlugForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_node_slug');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_redirect_slug'),
            ['slug_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['node_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_web_catalog_variant foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroContentVariantForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_variant');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['node_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add oro_web_catalog_node_scope foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroWebCatalogNodeScopeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_node_scope');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_scope'),
            ['scope_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['node_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_web_catalog_variant_scope foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroWebCatalogVariantScopeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_web_catalog_variant_scope');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_scope'),
            ['scope_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_variant'),
            ['variant_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
