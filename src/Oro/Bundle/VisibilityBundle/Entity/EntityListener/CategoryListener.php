<?php

namespace Oro\Bundle\VisibilityBundle\Entity\EntityListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Model\CategoryMessageHandler;

class CategoryListener
{
    /**
     * @var CategoryMessageHandler
     */
    protected $categoryMessageHandler;

    /**
     * @param CategoryMessageHandler $categoryMessageHandler
     */
    public function __construct(CategoryMessageHandler $categoryMessageHandler)
    {
        $this->categoryMessageHandler = $categoryMessageHandler;
    }

    /**
     * @param Category $category
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Category $category, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField(Category::FIELD_PARENT_CATEGORY)) {
            $this->categoryMessageHandler->addCategoryMessageToSchedule(
                'oro_visibility.visibility.category_position_change',
                $category
            );
        }
    }

    public function postRemove()
    {
        $this->categoryMessageHandler->addCategoryMessageToSchedule('oro_visibility.visibility.category_remove');
    }
}
