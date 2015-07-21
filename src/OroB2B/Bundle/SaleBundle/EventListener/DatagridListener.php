<?php

namespace OroB2B\Bundle\SaleBundle\EventListener;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class DatagridListener
{
    /**
     * @var string
     */
    protected $quoteClass;

    /**
     * @var string
     */
    protected $accountUserClass;

    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param string $quoteClass
     * @param string $accountUserClass
     * @param AclManager $aclManager
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        $quoteClass,
        $accountUserClass,
        AclManager $aclManager,
        SecurityFacade $securityFacade
    ) {
        $this->quoteClass = $quoteClass;
        $this->accountUserClass = $accountUserClass;
        $this->aclManager = $aclManager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBeforeFrontendQuotes(BuildBefore $event)
    {
        if (!$this->permissionShowUserColumn()) {
            $this->removeAccountUserNameColumn($event->getConfig());
        }
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function removeAccountUserNameColumn(DatagridConfiguration $config)
    {
        $config
            ->offsetUnsetByPath('[columns][accountUserName]')
            ->offsetUnsetByPath('[sorters][columns][accountUserName]')
            ->offsetUnsetByPath('[filters][columns][accountUserName]')
        ;
    }

    protected function permissionShowUserColumn()
    {
        if (!$this->securityFacade->isGranted(
            sprintf('%s;entity:%s', BasicPermissionMap::PERMISSION_VIEW, $this->accountUserClass)
        )) {
            return false;
        }
        $user = $this->securityFacade->getLoggedUser();

        foreach ($user->getRoles() as $role) {
            $acl = new ObjectIdentity('entity', $this->quoteClass);
            $aces = $this->aclManager->getAces($this->aclManager->getSid($role), $acl);
            foreach ($aces as $ace) {
                if ($ace->getMask() & EntityMaskBuilder::MASK_VIEW_LOCAL) {
                    return true;
                }
            }
        }

        return false;
    }
}
