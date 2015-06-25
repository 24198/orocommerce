<?php

namespace OroB2B\Bundle\CustomerBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroB2B\Bundle\CustomerBundle\Migrations\Schema\v1_0\OroB2BCustomerBundle as OroB2BCustomerBundle10;
use OroB2B\Bundle\CustomerBundle\Migrations\Schema\v1_0\OroB2BCustomerExtensions as OroB2BCustomerExtensions10;

class OroB2BCustomerBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    const TABLE_NAME = 'orob2b_customer';

    /** @var  ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
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
        $migration = new OroB2BCustomerBundle10();
        $migration->up($schema, $queries);

        $this->extendExtension->addEnumField(
            $schema,
            static::TABLE_NAME,
            'internal_rating',
            'cust_internal_rating'
        );
    }
}
