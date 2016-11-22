<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Functional\Visibility\Cache\Product;

use Oro\Bundle\CatalogBundle\Manager\ProductIndexScheduler;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\ProductVisibilityResolved;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\Repository\ProductRepository;
use Oro\Bundle\VisibilityBundle\Visibility\Cache\Product\ProductResolvedCacheBuilder;

/**
 * @group CommunityEdition
 * @dbIsolation
 */
class ProductResolvedCacheBuilderTest extends AbstractCacheBuilderTest
{
    /**
     * {@inheritdoc}
     */
    public function buildCacheDataProvider()
    {
        return [
            [
                'expectedStaticCount' => 3,
                'expectedCategoryCount' => 0,
            ],
        ];
    }

    /**
     * @return ProductRepository
     */
    protected function getRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(
            'OroVisibilityBundle:VisibilityResolved\ProductVisibilityResolved'
        );
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

        $builder = new ProductResolvedCacheBuilder(
            $container->get('doctrine'),
            $container->get('oro_scope.scope_manager'),
            $indexScheduler
        );
        $builder->setRepositoryHolder($this->getContainer()->get('oro_visibility.product_repository_holder'));
        $builder->setCacheClass(
            $container->getParameter('oro_visibility.entity.product_visibility_resolved.class')
        );

        return $builder;
    }
}
