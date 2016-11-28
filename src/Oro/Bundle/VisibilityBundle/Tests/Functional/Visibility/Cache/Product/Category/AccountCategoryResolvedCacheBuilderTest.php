<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Functional\Visibility\Cache\Product\Category;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Manager\ProductIndexScheduler;
use Oro\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\AccountCategoryVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CategoryVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\VisibilityInterface;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\AccountCategoryVisibilityResolved;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\BaseCategoryVisibilityResolved;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\Repository\AccountCategoryRepository;
use Oro\Bundle\VisibilityBundle\Visibility\Cache\Product\Category\AccountCategoryResolvedCacheBuilder;
use Oro\Bundle\VisibilityBundle\Visibility\Cache\Product\Category\Subtree\VisibilityChangeAccountSubtreeCacheBuilder;

/**
 * @dbIsolation
 */
class AccountCategoryResolvedCacheBuilderTest extends AbstractProductResolvedCacheBuilderTest
{
    /** @var Category */
    protected $category;

    /** @var Account */
    protected $account;

    /** @var AccountCategoryResolvedCacheBuilder */
    protected $builder;

    /**
     * @var ScopeManager
     */
    protected $scopeManager;

    protected function setUp()
    {
        parent::setUp();

        $this->category = $this->getReference(LoadCategoryData::SECOND_LEVEL1);
        $this->account = $this->getReference('account.level_1');

        $container = $this->client->getContainer();
        $this->scopeManager = $container->get('oro_scope.scope_manager');
        $this->scope = $this->scopeManager->findOrCreate(
            AccountCategoryVisibility::VISIBILITY_TYPE,
            ['account' => $this->account]
        );

        $indexScheduler = new ProductIndexScheduler(
            $container->get('oro_entity.doctrine_helper'),
            $container->get('event_dispatcher')
        );

        $this->builder = new AccountCategoryResolvedCacheBuilder(
            $container->get('doctrine'),
            $this->scopeManager,
            $indexScheduler,
            $container->get('oro_entity.orm.insert_from_select_query_executor')
        );
        $this->builder->setCacheClass(
            $container->getParameter('oro_visibility.entity.account_category_visibility_resolved.class')
        );
        $this->builder->setRepository(
            $container->get('oro_visibility.account_category_repository')
        );
        $subtreeBuilder = new VisibilityChangeAccountSubtreeCacheBuilder(
            $container->get('doctrine'),
            $container->get('oro_visibility.visibility.resolver.category_visibility_resolver'),
            $container->get('oro_config.manager'),
            $this->scopeManager
        );

        $this->builder->setVisibilityChangeAccountSubtreeCacheBuilder($subtreeBuilder);
    }

    public function testChangeAccountCategoryVisibilityToHidden()
    {
        $visibility = new AccountCategoryVisibility();
        $visibility->setCategory($this->category);
        $visibility->setScope($this->scope);
        $visibility->setVisibility(CategoryVisibility::HIDDEN);

        $em = $this->registry->getManagerForClass('OroVisibilityBundle:Visibility\AccountCategoryVisibility');
        $em->persist($visibility);
        $em->flush();
        $this->builder->buildCache();
        $visibilityResolved = $this->getVisibilityResolved();
        $this->assertStatic($visibilityResolved, $visibility, BaseCategoryVisibilityResolved::VISIBILITY_HIDDEN);
    }

    /**
     * @depends testChangeAccountCategoryVisibilityToHidden
     */
    public function testChangeAccountCategoryVisibilityToVisible()
    {
        $visibility = $this->getVisibility();
        $visibility->setVisibility(CategoryVisibility::VISIBLE);

        $em = $this->registry->getManagerForClass('OroVisibilityBundle:Visibility\AccountCategoryVisibility');
        $em->flush();
        $this->builder->buildCache();
        $visibilityResolved = $this->getVisibilityResolved();
        $this->assertStatic($visibilityResolved, $visibility, BaseCategoryVisibilityResolved::VISIBILITY_VISIBLE);
    }

    /**
     * @depends testChangeAccountCategoryVisibilityToHidden
     */
    public function testChangeAccountCategoryVisibilityToAll()
    {
        $visibility = $this->getVisibility();
        $visibility->setVisibility(AccountCategoryVisibility::CATEGORY);


        $accountCategoryVisibility = $this->getVisibility();
        $accountCategoryVisibility->setVisibility(AccountCategoryVisibility::CATEGORY);
        $em = $this->registry->getManagerForClass(AccountCategoryVisibility::class);
        $em->flush();

        $this->builder->buildCache();

        $visibilityResolved = $this->getVisibilityResolved();
        $this->assertEquals(
            $accountCategoryVisibility->getVisibility(),
            $visibilityResolved['sourceCategoryVisibility']['visibility']
        );
        $this->assertEquals(BaseCategoryVisibilityResolved::SOURCE_STATIC, $visibilityResolved['source']);
        $this->assertEquals($this->category->getId(), $visibilityResolved['category_id']);
        $this->assertEquals(
            BaseCategoryVisibilityResolved::VISIBILITY_VISIBLE,
            $visibilityResolved['visibility']
        );
    }

    /**
     * @depends testChangeAccountCategoryVisibilityToAll
     */
    public function testChangeAccountCategoryVisibilityToParentCategory()
    {
        $visibility = $this->getVisibility();
        $visibility->setVisibility(AccountCategoryVisibility::PARENT_CATEGORY);

        $em = $this->registry->getManagerForClass('OroVisibilityBundle:Visibility\AccountCategoryVisibility');
        $em->flush();
        $this->builder->buildCache();
        $visibilityResolved = $this->getVisibilityResolved();
        $this->assertEquals(
            $visibility->getVisibility(),
            $visibilityResolved['sourceCategoryVisibility']['visibility']
        );
        $this->assertEquals(BaseCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY, $visibilityResolved['source']);
        $this->assertEquals($this->category->getId(), $visibilityResolved['category_id']);
        $this->assertEquals(
            BaseCategoryVisibilityResolved::VISIBILITY_FALLBACK_TO_CONFIG,
            $visibilityResolved['visibility']
        );
    }

    /**
     * @depends testChangeAccountCategoryVisibilityToParentCategory
     */
    public function testChangeAccountCategoryVisibilityToAccountGroup()
    {
        $visibility = $this->getVisibility();
        $visibility->setVisibility(AccountCategoryVisibility::ACCOUNT_GROUP);

        $this->assertNotNull($this->getVisibilityResolved());

        $em = $this->registry->getManagerForClass('OroVisibilityBundle:Visibility\AccountCategoryVisibility');
        $em->flush();

        $this->assertNull($this->getVisibilityResolved());
    }

    /**
     * @dataProvider buildCacheDataProvider
     * @param array $expectedVisibilities
     */
    public function testBuildCache(array $expectedVisibilities)
    {
        $expectedVisibilities = $this->replaceReferencesWithIds($expectedVisibilities);
        usort($expectedVisibilities, [$this, 'sortByCategoryAndScope']);

        $this->builder->buildCache();

        $actualVisibilities = $this->getResolvedVisibilities();
        usort($actualVisibilities, [$this, 'sortByCategoryAndScope']);

        $this->assertEquals($expectedVisibilities, $actualVisibilities);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildCacheDataProvider()
    {
        return [
            [
                'expectedVisibilities' => [
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_FALLBACK_TO_CONFIG,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1'
                    ],
                    [
                        'category' => 'category_1_5_6',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1'
                    ],
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_2_3',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_2_3_4',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_5',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_5_6',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.1'
                    ],
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_FALLBACK_TO_CONFIG,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_FALLBACK_TO_CONFIG,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2'
                    ],
                    [
                        'category' => 'category_1_5',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.2'
                    ],
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.2.1'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1'
                    ],
                    [
                        'category' => 'category_1_2_3',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1'
                    ],
                    [
                        'category' => 'category_1_2_3_4',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.2.1'
                    ],
                    [
                        'category' => 'category_1_5',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1.1'
                    ],
                    [
                        'category' => 'category_1_5_6',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1.1'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.2.1.1'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.3.1'
                    ],
                    [
                        'category' => 'category_1_5_6',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.3.1'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.3.1'
                    ],
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1_2_3',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1_2_3_4',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_HIDDEN,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1_5',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1_5_6',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_PARENT_CATEGORY,
                        'account' => 'account.level_1.3.1.1'
                    ],
                    [
                        'category' => 'category_1',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.4'
                    ],
                    [
                        'category' => 'category_1_2',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.4'
                    ],
                    [
                        'category' => 'category_1_2_3_4',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.4'
                    ],
                    [
                        'category' => 'category_1_5',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.4'
                    ],
                    [
                        'category' => 'category_1_5_6_7',
                        'visibility' => AccountCategoryVisibilityResolved::VISIBILITY_VISIBLE,
                        'source' => AccountCategoryVisibilityResolved::SOURCE_STATIC,
                        'account' => 'account.level_1.4'
                    ],
                ]
            ]
        ];
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function sortByCategoryAndScope(array $a, array $b)
    {
        if ($a['category'] == $b['category']) {
            return $a['account'] > $b['account'] ? 1 : -1;
        }

        return $a['category'] > $b['category'] ? 1 : -1;
    }

    /**
     * @param array $visibilities
     * @return array
     */
    protected function replaceReferencesWithIds(array $visibilities)
    {
        $rootCategory = $this->getRootCategory();
        foreach ($visibilities as $key => $row) {
            $category = $row['category'];
            /** @var Category $category */
            if ($category === self::ROOT) {
                $category = $rootCategory;
            } else {
                $category = $this->getReference($category);
            }

            $visibilities[$key]['category'] = $category->getId();

            /** @var Account $category */
            $account = $this->getReference($row['account']);
            $visibilities[$key]['account'] = $account->getId();
        }
        return $visibilities;
    }

    /**
     * @return array
     */
    protected function getResolvedVisibilities()
    {
        /** @var AccountCategoryRepository $repository */
        $repository = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroVisibilityBundle:VisibilityResolved\AccountCategoryVisibilityResolved')
            ->getRepository('OroVisibilityBundle:VisibilityResolved\AccountCategoryVisibilityResolved');

        return $repository
            ->createQueryBuilder('entity')
            ->select(
                'IDENTITY(entity.category) as category',
                'IDENTITY(scope.account) as account',
                'entity.visibility',
                'entity.source'
            )
            ->join('entity.scope', 'scope')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array
     */
    protected function getVisibilityResolved()
    {
        $em = $this->registry
            ->getManagerForClass('OroVisibilityBundle:VisibilityResolved\AccountCategoryVisibilityResolved');
        /** @var AccountCategoryRepository $repository */
        $repository = $em->getRepository('OroVisibilityBundle:VisibilityResolved\AccountCategoryVisibilityResolved');
        $qb = $repository
            ->createQueryBuilder('accountCategoryVisibilityResolved');
        $entity = $qb->select('accountCategoryVisibilityResolved', 'accountCategoryVisibility')
            ->leftJoin('accountCategoryVisibilityResolved.sourceCategoryVisibility', 'accountCategoryVisibility')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('accountCategoryVisibilityResolved.category', ':category'),
                    $qb->expr()->eq('accountCategoryVisibilityResolved.scope', ':scope')
                )
            )
            ->setParameters([
                'category' => $this->category,
                'scope' => $this->scope,
            ])
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return $entity;
    }

    /**
     * @return null|AccountCategoryVisibility
     */
    protected function getVisibility()
    {
        return $this->registry->getManagerForClass('OroVisibilityBundle:Visibility\AccountCategoryVisibility')
            ->getRepository('OroVisibilityBundle:Visibility\AccountCategoryVisibility')
            ->findOneBy(['category' => $this->category, 'scope' => $this->scope]);
    }

    /**
     * @param array $categoryVisibilityResolved
     * @param VisibilityInterface $categoryVisibility
     * @param integer $expectedVisibility
     */
    protected function assertStatic(
        array $categoryVisibilityResolved,
        VisibilityInterface $categoryVisibility,
        $expectedVisibility
    ) {
        $this->assertNotNull($categoryVisibilityResolved);
        $this->assertEquals($this->category->getId(), $categoryVisibilityResolved['category_id']);
        $this->assertEquals($this->scope->getId(), $categoryVisibilityResolved['scope_id']);
        $this->assertEquals(AccountCategoryVisibilityResolved::SOURCE_STATIC, $categoryVisibilityResolved['source']);
        $this->assertEquals(
            $categoryVisibility->getVisibility(),
            $categoryVisibilityResolved['sourceCategoryVisibility']['visibility']
        );
        $this->assertEquals($expectedVisibility, $categoryVisibilityResolved['visibility']);
    }
}
