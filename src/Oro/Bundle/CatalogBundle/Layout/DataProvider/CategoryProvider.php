<?php

namespace Oro\Bundle\CatalogBundle\Layout\DataProvider;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Oro\Bundle\CatalogBundle\Handler\RequestProductHandler;
use Oro\Bundle\CatalogBundle\Provider\CategoryTreeProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class CategoryProvider
{
    /** @var array */
    protected $categories = [];

    /** @var array */
    protected $tree = [];

    /** @var CategoryRepository  */
    protected $categoryRepository;

    /** @var RequestProductHandler  */
    protected $requestProductHandler;

    /** @var CategoryTreeProvider */
    protected $categoryTreeProvider;

    /**
     * @param RequestProductHandler $requestProductHandler
     * @param CategoryRepository $categoryRepository
     * @param CategoryTreeProvider $categoryTreeProvider
     */
    public function __construct(
        RequestProductHandler $requestProductHandler,
        CategoryRepository $categoryRepository,
        CategoryTreeProvider $categoryTreeProvider
    ) {
        $this->requestProductHandler = $requestProductHandler;
        $this->categoryRepository = $categoryRepository;
        $this->categoryTreeProvider = $categoryTreeProvider;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->loadCategory((int) $this->requestProductHandler->getCategoryId());
    }

    /**
     * @return Category
     */
    public function getRootCategory()
    {
        return $this->loadCategory();
    }

    /**
     * @deprecated use CategoryBreadcrumbProvider::getItems() instead
     * @return Category[]
     */
    public function getBreadcrumbs()
    {
        $categories = array_merge($this->getParentCategories(), [$this->getCurrentCategory()]);
        $breadcrumbs = [];

        /* @var Category $category */
        foreach ($categories as $category) {
            $breadcrumbs[] = [
                'label_localized' => $category->getTitles(),
                'route' => 'oro_product_frontend_product_index',
                'routeParams' => [
                    'categoryId' => $category->getId(),
                    'includeSubcategories' => $this->requestProductHandler->getIncludeSubcategoriesChoice()
                ]
            ];
        }

        return $breadcrumbs;
    }

    /**
     * @param CustomerUser|null $user
     *
     * @return Category[]
     */
    public function getCategoryTree(CustomerUser $user = null)
    {
        $userId = $user ? $user->getId() : 0;
        if (!array_key_exists($userId, $this->tree)) {
            $rootCategory = $this->loadCategory();

            $this->tree[$userId] = [];
            if ($rootCategory) {
                /** @var DTO\Category[] $categoryDTOs */
                $categoryDTOs = [];
                $categoryDTOs[$rootCategory->getMaterializedPath()] = new DTO\Category($rootCategory);
                $categories = $this->categoryTreeProvider->getCategories($user, $rootCategory, false);
                foreach ($categories as $category) {
                    $dto = new DTO\Category($category);
                    $categoryDTOs[$category->getMaterializedPath()] = $dto;
                    if ($category->getParentCategory()) {
                        $categoryDTOs[$category->getParentCategory()->getMaterializedPath()]
                            ->addChildCategory($dto);
                    }
                }

                $this->tree[$userId] = $categoryDTOs[$rootCategory->getMaterializedPath()]->getChildCategories();
                unset($categoryDTOs);
            }
        }

        return $this->tree[$userId];
    }

    /**
     * @return array
     */
    public function getParentCategories()
    {
        // we don't need current category in the path, so let's start from parent category
        $parent = $this->getCurrentCategory()->getParentCategory();

        if ($parent !== null) {
            $parents = $this->categoryRepository->getPath($parent);
            return is_array($parents) ? $parents : [];
        } else {
            return [];
        }
    }

    /**
     * @param int $categoryId
     *
     * @return Category
     */
    protected function loadCategory($categoryId = 0)
    {
        if (!array_key_exists($categoryId, $this->categories)) {
            if ($categoryId) {
                $this->categories[$categoryId] = $this->categoryRepository->find($categoryId);
            } else {
                $this->categories[$categoryId] = $this->categoryRepository->getMasterCatalogRoot();
            }
        }

        return $this->categories[$categoryId];
    }

    /**
     * @return bool
     */
    public function getIncludeSubcategoriesChoice()
    {
        return $this->requestProductHandler->getIncludeSubcategoriesChoice();
    }
}
