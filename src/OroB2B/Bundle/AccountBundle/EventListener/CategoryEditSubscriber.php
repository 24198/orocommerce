<?php

namespace OroB2B\Bundle\AccountBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\AccountBundle\Entity\AccountGroupCategoryVisibility;
use OroB2B\Bundle\AccountBundle\Entity\AccountCategoryVisibility;
use OroB2B\Bundle\CatalogBundle\Entity\Category;
use OroB2B\Bundle\CatalogBundle\Event\CategoryEditEvent;

class CategoryEditSubscriber implements EventSubscriberInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;
    /** @var EnumValueProvider */
    protected $enumValueProvider;
    /** @var  ArrayCollection|EntityManager[] */
    protected $entityManagers;

    /**
     * @param DoctrineHelper    $doctrineHelper
     * @param EnumValueProvider $enumValueProvider
     */
    public function __construct(DoctrineHelper $doctrineHelper, EnumValueProvider $enumValueProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->enumValueProvider = $enumValueProvider;
        $this->entityManagers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [CategoryEditEvent::NAME => 'onCategoryEdit'];
    }

    /**
     * @param CategoryEditEvent $event
     */
    public function onCategoryEdit(CategoryEditEvent $event)
    {
        $category = $event->getCategory();
        $accountChangeSet = $event->getForm()->get('visibilityForAccount')->getData();
        $accountGroupChangeSet = $event->getForm()->get('visibilityForAccountGroup')->getData();

        foreach ($accountChangeSet as $item) {
            /** @var Account $account */
            $account = $item['entity'];
            $this->processAccountVisibility($category, $account, $item['data']['visibility']);
        }

        foreach ($accountGroupChangeSet as $item) {
            /** @var AccountGroup $accountGroup */
            $accountGroup = $item['entity'];
            $this->processAccountGroupVisibility($category, $accountGroup, $item['data']['visibility']);
        }

        foreach ($this->entityManagers as $em) {
            $em->flush();
        }
    }

    /**
     * @param Category $category
     * @param Account  $account
     * @param string   $visibilityCode
     */
    protected function processAccountVisibility(Category $category, Account $account, $visibilityCode)
    {
        $accountCategoryVisibility = $this
            ->doctrineHelper
            ->getEntityRepository('OroB2BAccountBundle:AccountCategoryVisibility')
            ->findOneBy(['category' => $category, 'account' => $account]);

        if (!$accountCategoryVisibility) {
            $accountCategoryVisibility = new AccountCategoryVisibility();
            $accountCategoryVisibility->setCategory($category);
            $accountCategoryVisibility->setAccount($account);
        }

        $this->applyVisibility($accountCategoryVisibility, 'acc_ctgry_visibility', $visibilityCode);
    }

    /**
     * @param Category     $category
     * @param AccountGroup $accountGroup
     * @param              $visibilityCode
     */
    protected function processAccountGroupVisibility(Category $category, AccountGroup $accountGroup, $visibilityCode)
    {
        $accountGroupCategoryVisibility = $this
            ->doctrineHelper
            ->getEntityRepository('OroB2BAccountBundle:AccountGroupCategoryVisibility')
            ->findOneBy(['category' => $category, 'accountGroup' => $accountGroup]);

        if (!$accountGroupCategoryVisibility) {
            $accountGroupCategoryVisibility = new AccountGroupCategoryVisibility();
            $accountGroupCategoryVisibility->setCategory($category);
            $accountGroupCategoryVisibility->setAccountGroup($accountGroup);
        }

        $this->applyVisibility($accountGroupCategoryVisibility, 'acc_grp_ctgry_vsblity', $visibilityCode);
    }

    /**
     * @param AccountCategoryVisibility|AccountGroupCategoryVisibility $visibilityEntity
     * @param string                                                   $enumCode
     * @param string                                                   $visibilityCode
     */
    protected function applyVisibility($visibilityEntity, $enumCode, $visibilityCode)
    {
        $entityClass = get_class($visibilityEntity);
        $visibility = $this->enumValueProvider->getEnumValueByCode($enumCode, $visibilityCode);
        $visibilityEntity->setVisibility($visibility);

        if (!$this->entityManagers->offsetExists($entityClass)) {
            $em = $this->doctrineHelper->getEntityManager($visibilityEntity);
            $this->entityManagers->offsetSet($entityClass, $em);
        } else {
            $em = $this->entityManagers->offsetGet($entityClass);
        }

        $em->persist($visibilityEntity);
    }
}
