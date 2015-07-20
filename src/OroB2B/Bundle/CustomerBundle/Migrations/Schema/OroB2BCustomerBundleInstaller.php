<?php

namespace OroB2B\Bundle\CustomerBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

use OroB2B\Bundle\CustomerBundle\Entity\Customer;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OroB2BCustomerBundleInstaller implements
    Installation,
    NoteExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface
{

    const ORO_B2B_CUSTOMER_TABLE_NAME = 'orob2b_customer';
    const ORO_B2B_ACCOUNT_USER_TABLE_NAME = 'orob2b_account_user';
    const ORO_B2B_ACC_USER_ACCESS_ROLE_TABLE_NAME = 'orob2b_acc_user_access_role';
    const ORO_B2B_CUSTOMER_GROUP_TABLE_NAME = 'orob2b_customer_group';
    const ORO_B2B_ACCOUNT_USER_ORG_TABLE_NAME = 'orob2b_account_user_org';
    const ORO_B2B_ACCOUNT_USER_ROLE_TABLE_NAME = 'orob2b_account_user_role';
    const ORO_B2B_ACCOUNT_ROLE_TO_WEBSITE_TABLE_NAME = 'orob2b_account_role_to_website';
    const ORO_B2B_WEBSITE_TABLE_NAME = 'orob2b_website';
    const ORO_ORGANIZATION_TABLE_NAME = 'oro_organization';
    const ORO_EMAIL = 'oro_email';
    const ORO_CALENDAR_EVENT = 'oro_calendar_event';

    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * Sets the AttachmentExtension
     *
     * @param AttachmentExtension $attachmentExtension
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * Sets the NoteExtension
     *
     * @param NoteExtension $noteExtension
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroB2BAccountUserTable($schema);
        $this->createOroB2BAccountUserOrganizationTable($schema);
        $this->createOroB2BAccountUserRoleTable($schema);
        $this->createOroB2BAccountUserAccessAccountUserRoleTable($schema);
        $this->createOroB2BAccountUserRoleToWebsiteTable($schema);
        $this->createOroB2BCustomerTable($schema);
        $this->createOroB2BCustomerGroupTable($schema);
        $this->createOroB2BAuditFieldTable($schema);
        $this->createOroB2BAuditTable($schema);

        /** Foreign keys generation **/
        $this->addOroB2BAccountUserForeignKeys($schema);
        $this->addOroB2BAccountUserAccessAccountUserRoleForeignKeys($schema);
        $this->addOroB2BAccountUserOrganizationForeignKeys($schema);
        $this->addOroB2BAccountUserRoleToWebsiteForeignKeys($schema);
        $this->addOroB2BCustomerForeignKeys($schema);
        $this->addOroB2BAuditFieldForeignKeys($schema);
        $this->addOroB2BAuditForeignKeys($schema);
    }

    /**
     * Create orob2b_account_user table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAccountUserTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_ACCOUNT_USER_TABLE_NAME);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('birthday', 'date', ['notnull' => false]);
        $table->addColumn('enabled', 'boolean', []);
        $table->addColumn('confirmed', 'boolean', []);
        $table->addColumn('salt', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('confirmation_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('password_requested', 'datetime', ['notnull' => false]);
        $table->addColumn('password_changed', 'datetime', ['notnull' => false]);
        $table->addColumn('last_login', 'datetime', ['notnull' => false]);
        $table->addColumn('login_count', 'integer', ['default' => '0', 'unsigned' => true]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);

        $table->setPrimaryKey(['id']);

        $table->addUniqueIndex(['username'], 'UNIQ_689CD865F85E0677');
        $table->addUniqueIndex(['email'], 'UNIQ_689CD865E7927C74');

        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            static::ORO_B2B_ACCOUNT_USER_TABLE_NAME,
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ]
        );

        $this->noteExtension->addNoteAssociation($schema, static::ORO_B2B_ACCOUNT_USER_TABLE_NAME);

        $this->activityExtension->addActivityAssociation(
            $schema,
            static::ORO_EMAIL,
            static::ORO_B2B_ACCOUNT_USER_TABLE_NAME
        );
        $this->activityExtension->addActivityAssociation(
            $schema,
            static::ORO_CALENDAR_EVENT,
            static::ORO_B2B_ACCOUNT_USER_TABLE_NAME
        );
    }

    /**
     * Create orob2b_customer table
     *
     * @param Schema $schema
     */
    protected function createOroB2BCustomerTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_CUSTOMER_TABLE_NAME);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['name'], 'orob2b_customer_name_idx', []);

        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            static::ORO_B2B_CUSTOMER_TABLE_NAME,
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ]
        );

        $this->noteExtension->addNoteAssociation($schema, static::ORO_B2B_CUSTOMER_TABLE_NAME);
        $this->extendExtension->addEnumField(
            $schema,
            static::ORO_B2B_CUSTOMER_TABLE_NAME,
            'internal_rating',
            Customer::INTERNAL_RATING_CODE
        );
    }

    /**
     * Create orob2b_account_user_access_user_role table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAccountUserAccessAccountUserRoleTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_ACC_USER_ACCESS_ROLE_TABLE_NAME);

        $table->addColumn('account_user_id', 'integer', []);
        $table->addColumn('account_user_role_id', 'integer', []);

        $table->setPrimaryKey(['account_user_id', 'account_user_role_id']);
    }


    /**
     * Create orob2b_customer_group table
     *
     * @param Schema $schema
     */
    protected function createOroB2BCustomerGroupTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_CUSTOMER_GROUP_TABLE_NAME);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['name'], 'orob2b_customer_group_name_idx', []);
    }

    /**
     * Create orob2b_audit_field table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAuditFieldTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_audit_field');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('audit_id', 'integer', []);
        $table->addColumn('field', 'string', ['length' => 255]);
        $table->addColumn('data_type', 'string', ['length' => 255]);
        $table->addColumn('old_integer', 'bigint', ['notnull' => false]);
        $table->addColumn('old_float', 'float', ['notnull' => false]);
        $table->addColumn('old_boolean', 'boolean', ['notnull' => false]);
        $table->addColumn('old_text', 'text', ['notnull' => false]);
        $table->addColumn('old_date', 'date', ['notnull' => false, 'comment' => '(DC2Type:date)']);
        $table->addColumn('old_time', 'time', ['notnull' => false, 'comment' => '(DC2Type:time)']);
        $table->addColumn('old_datetime', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('new_integer', 'bigint', ['notnull' => false]);
        $table->addColumn('new_float', 'float', ['notnull' => false]);
        $table->addColumn('new_boolean', 'boolean', ['notnull' => false]);
        $table->addColumn('new_text', 'text', ['notnull' => false]);
        $table->addColumn('new_date', 'date', ['notnull' => false, 'comment' => '(DC2Type:date)']);
        $table->addColumn('new_time', 'time', ['notnull' => false, 'comment' => '(DC2Type:time)']);
        $table->addColumn('new_datetime', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orob2b_audit table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAuditTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_audit');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_user_id', 'integer', []);
        $table->addColumn('object_name', 'string', ['length' => 255]);
        $table->addColumn('action', 'string', ['length' => 8]);
        $table->addColumn('logged_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('object_id', 'integer', ['notnull' => false]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('version', 'integer', []);
        $table->addIndex(['logged_at'], 'idx_orob2b_audit_logged_at', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orob2b_account_user_organization table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAccountUserOrganizationTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_ACCOUNT_USER_ORG_TABLE_NAME);

        $table->addColumn('account_user_id', 'integer', []);
        $table->addColumn('organization_id', 'integer', []);

        $table->setPrimaryKey(['account_user_id', 'organization_id']);
    }

    /**
     * Create orob2b_account_user_role table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAccountUserRoleTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_ACCOUNT_USER_ROLE_TABLE_NAME);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('role', 'string', ['length' => 64]);
        $table->addColumn('label', 'string', ['length' => 64]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['role'], 'uniq_e153330157698a6a');
    }

    /**
     * Create orob2b_account_role_to_website table
     *
     * @param Schema $schema
     */
    protected function createOroB2BAccountUserRoleToWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable(static::ORO_B2B_ACCOUNT_ROLE_TO_WEBSITE_TABLE_NAME);
        $table->addColumn('account_user_role_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->setPrimaryKey(['account_user_role_id', 'website_id']);
        $table->addUniqueIndex(['website_id'], 'UNIQ_EC532EDD18F45C82');
    }

    /**
     * Add orob2b_account_user foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAccountUserForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::ORO_B2B_ACCOUNT_USER_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_ORGANIZATION_TABLE_NAME),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_CUSTOMER_TABLE_NAME),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_account_user_access_user_role foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAccountUserAccessAccountUserRoleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::ORO_B2B_ACC_USER_ACCESS_ROLE_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_ACCOUNT_USER_ROLE_TABLE_NAME),
            ['account_user_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_ACCOUNT_USER_TABLE_NAME),
            ['account_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }


    /**
     * Add orob2b_customer foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::ORO_B2B_CUSTOMER_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_CUSTOMER_GROUP_TABLE_NAME),
            ['group_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $table,
            ['parent_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_ORGANIZATION_TABLE_NAME),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_account_user_organization foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAccountUserOrganizationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::ORO_B2B_ACCOUNT_USER_ORG_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_ACCOUNT_USER_TABLE_NAME),
            ['account_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_ORGANIZATION_TABLE_NAME),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_account_role_to_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAccountUserRoleToWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::ORO_B2B_ACCOUNT_ROLE_TO_WEBSITE_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_WEBSITE_TABLE_NAME),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(static::ORO_B2B_ACCOUNT_USER_ROLE_TABLE_NAME),
            ['account_user_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orob2b_audit_field foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAuditFieldForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_audit_field');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_audit'),
            ['audit_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orob2b_audit foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BAuditForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_audit');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_account_user'),
            ['account_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
