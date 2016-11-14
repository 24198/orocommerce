<?php

namespace Oro\Bundle\CustomerBundle\Visibility;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\AccountGroup;
use Oro\Bundle\CustomerBundle\Provider\VisibilityScopeProvider;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\VisibilityInterface;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\AccountProductVisibilityResolved;
use Oro\Bundle\VisibilityBundle\Entity\VisibilityResolved\BaseVisibilityResolved;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;

trait ProductVisibilityTrait
{
    /**
     * @var string
     */
    protected $productConfigPath;

    /**
     * @var string
     */
    protected $categoryConfigPath;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var array
     */
    protected $configValue = [];

    /**
     * @var VisibilityScopeProvider
     */
    protected $visibilityScopeProvider;

    /**
     * @param QueryBuilder $queryBuilder
     * @param AccountGroup $accountGroup
     * @param Website $website
     * @return string
     */
    private function getAccountGroupProductVisibilityResolvedTermByWebsite(
        QueryBuilder $queryBuilder,
        AccountGroup $accountGroup,
        Website $website
    ) {
        $queryBuilder->leftJoin(
            'OroVisibilityBundle:VisibilityResolved\AccountGroupProductVisibilityResolved',
            'account_group_product_visibility_resolved',
            Join::WITH,
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(
                    $this->getRootAlias($queryBuilder),
                    'account_group_product_visibility_resolved.product'
                ),
                $queryBuilder->expr()->eq(
                    'account_group_product_visibility_resolved.scope',
                    ':accountGroupScope'
                )
            )
        );

        $scope = $this->getVisibilityScopeProvider()->getAccountGroupProductVisibilityScope($accountGroup, $website);

        $queryBuilder->setParameter('accountGroupScope', $scope);

        return sprintf(
            'COALESCE(%s, 0) * 10',
            $this->addCategoryConfigFallback('account_group_product_visibility_resolved.visibility')
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Account $account
     * @param Website $website
     * @return string
     */
    private function getAccountProductVisibilityResolvedTermByWebsite(
        QueryBuilder $queryBuilder,
        Account $account,
        Website $website
    ) {
        $queryBuilder->leftJoin(
            'OroVisibilityBundle:VisibilityResolved\AccountProductVisibilityResolved',
            'account_product_visibility_resolved',
            Join::WITH,
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(
                    $this->getRootAlias($queryBuilder),
                    'account_product_visibility_resolved.product'
                ),
                $queryBuilder->expr()->eq('account_product_visibility_resolved.scope', ':accountScope')
            )
        );

        $scope = $this->getVisibilityScopeProvider()->getAccountProductVisibilityScope($account, $website);

        $queryBuilder->setParameter('accountScope', $scope);

        $productFallback = $this->addCategoryConfigFallback('product_visibility_resolved.visibility');
        $accountFallback = $this->addCategoryConfigFallback('account_product_visibility_resolved.visibility');

        return $this->getAccountProductVisibilityResolvedVisibilityTerm($productFallback, $accountFallback);
    }

    /**
     * @param string $productFallback
     * @param string $accountFallback
     * @return string
     */
    private function getAccountProductVisibilityResolvedVisibilityTerm($productFallback, $accountFallback)
    {
        $term = <<<TERM
CASE WHEN account_product_visibility_resolved.visibility = %s
    THEN (COALESCE(%s, %s) * 100)
ELSE (COALESCE(%s, 0) * 100)
END
TERM;
        return sprintf(
            $term,
            AccountProductVisibilityResolved::VISIBILITY_FALLBACK_TO_ALL,
            $productFallback,
            $this->getProductConfigValue(),
            $accountFallback
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Website $website
     * @return string
     */
    private function getProductVisibilityResolvedTermByWebsite(QueryBuilder $queryBuilder, Website $website)
    {
        $queryBuilder->leftJoin(
            'OroVisibilityBundle:VisibilityResolved\ProductVisibilityResolved',
            'product_visibility_resolved',
            Join::WITH,
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq($this->getRootAlias($queryBuilder), 'product_visibility_resolved.product'),
                $queryBuilder->expr()->eq('product_visibility_resolved.scope', ':scope')
            )
        );

        $queryBuilder->setParameter('scope', $this->getVisibilityScopeProvider()->getProductVisibilityScope($website));

        return sprintf(
            'COALESCE(%s, %s)',
            $this->addCategoryConfigFallback('product_visibility_resolved.visibility'),
            $this->getProductConfigValue()
        );
    }

    /**
     * @param string $field
     * @return string
     */
    protected function addCategoryConfigFallback($field)
    {
        return sprintf(
            'CASE WHEN %1$s = %2$s THEN %3$s ELSE %1$s END',
            $field,
            BaseVisibilityResolved::VISIBILITY_FALLBACK_TO_CONFIG,
            $this->getCategoryConfigValue()
        );
    }

    /**
     * @return int
     */
    protected function getProductConfigValue()
    {
        return $this->getConfigValue($this->productConfigPath);
    }

    /**
     * @return int
     */
    protected function getCategoryConfigValue()
    {
        return $this->getConfigValue($this->categoryConfigPath);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return mixed
     */
    protected function getRootAlias(QueryBuilder $queryBuilder)
    {
        $aliases = $queryBuilder->getRootAliases();

        return reset($aliases);
    }

    /**
     * @param string $path
     * @return integer
     * @throws \LogicException
     */
    protected function getConfigValue($path)
    {
        if (!empty($this->configValue[$path])) {
            return $this->configValue[$path];
        }

        if (!$this->productConfigPath) {
            throw new \LogicException(
                sprintf('%s::productConfigPath not configured', get_class($this))
            );
        }
        if (!$this->categoryConfigPath) {
            throw new \LogicException(
                sprintf('%s::categoryConfigPath not configured', get_class($this))
            );
        }

        $this->configValue = [
            $this->productConfigPath => $this->configManager->get($this->productConfigPath),
            $this->categoryConfigPath => $this->configManager->get($this->categoryConfigPath),
        ];

        foreach ($this->configValue as $key => $value) {
            $this->configValue[$key] = $value === VisibilityInterface::VISIBLE
                ? BaseVisibilityResolved::VISIBILITY_VISIBLE
                : BaseVisibilityResolved::VISIBILITY_HIDDEN;
        }

        return $this->configValue[$path];
    }

    /**
     * @param string $path
     */
    public function setProductVisibilitySystemConfigurationPath($path)
    {
        $this->productConfigPath = $path;
    }

    /**
     * @param string $path
     */
    public function setCategoryVisibilitySystemConfigurationPath($path)
    {
        $this->categoryConfigPath = $path;
    }

    /**
     * @param VisibilityScopeProvider $visibilityScopeProvider
     */
    public function setVisibilityScopeProvider(VisibilityScopeProvider $visibilityScopeProvider)
    {
        $this->visibilityScopeProvider = $visibilityScopeProvider;
    }

    /**
     * @return VisibilityScopeProvider
     * @throws \RuntimeException
     */
    public function getVisibilityScopeProvider()
    {
        if (!$this->visibilityScopeProvider) {
            throw new \RuntimeException('Visibility scope provider was not set');
        }

        return $this->visibilityScopeProvider;
    }
}
