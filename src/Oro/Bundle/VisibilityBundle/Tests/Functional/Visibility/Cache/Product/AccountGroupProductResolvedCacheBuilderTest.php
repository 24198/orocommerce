<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Functional\Visibility\Cache\Product;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CatalogBundle\Manager\ProductIndexScheduler;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\Repository\AccountGroupProductRepository;
use Oro\Bundle\VisibilityBundle\Visibility\Cache\Product\AccountGroupProductResolvedCacheBuilder;

/**
 * @dbIsolation
 */
class AccountGroupProductResolvedCacheBuilderTest extends AbstractCacheBuilderTest
{
    /**
     * {@inheritdoc}
     */
    public function buildCacheDataProvider()
    {
        return [
            [
                'expectedStaticCount' => 8,
                'expectedCategoryCount' => 2,
            ],
        ];
    }

    /**
     * @return AccountGroupProductRepository|EntityRepository
     */
    protected function getRepository()
    {
        return $this->getContainer()->get('oro_visibility.account_group_product_repository');
    }

    /**
     * {@inheritdoc}
     */
    protected function getCacheBuilder()
    {
        $container = $this->client->getContainer();

        $indexScheduler = new ProductIndexScheduler(
            $container->get('oro_entity.doctrine_helper'),
            $container->get('event_dispatcher')
        );

        $builder = new AccountGroupProductResolvedCacheBuilder(
            $container->get('doctrine'),
            $container->get('oro_scope.scope_manager'),
            $indexScheduler,
            $container->get('oro_entity.orm.insert_from_select_query_executor')
        );
        $builder->setCacheClass(
            $container->getParameter('oro_visibility.entity.account_group_product_visibility_resolved.class')
        );
        $builder->setRepository($container->get('oro_visibility.account_group_product_repository'));

        return $builder;
    }
}
