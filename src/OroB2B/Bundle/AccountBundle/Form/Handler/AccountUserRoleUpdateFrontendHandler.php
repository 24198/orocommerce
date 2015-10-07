<?php

namespace OroB2B\Bundle\AccountBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Oro\Bundle\UserBundle\Entity\AbstractRole;

use OroB2B\Bundle\AccountBundle\Entity\AccountUser;
use OroB2B\Bundle\AccountBundle\Entity\AccountUserRole;
use OroB2B\Bundle\AccountBundle\Entity\Repository\AccountUserRoleRepository;
use OroB2B\Bundle\AccountBundle\Form\Type\FrontendAccountUserRoleType;

class AccountUserRoleUpdateFrontendHandler extends AbstractAccountUserRoleHandler
{
    /**
     * @var AccountUserRole
     */
    protected $newRole;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var array
     */
    protected $appendUsers = [];

    /**
     * @var AccountUser
     */
    protected $loggedAccountUser;

    /** @var  RequestStack */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritDoc}
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function onSuccess(AbstractRole $role, array $appendUsers, array $removeUsers)
    {
        // TODO: When task BB-1046 will be done, remove method removeOriginalRoleFromUsers.
        // In method addNewRoleToUsers before addRole add method removeRole($role). Also needs delete flush;

        /** @var AccountUserRole $role */
        if ($role->getId()) {
            /** @var AccountUserRoleRepository $roleRepository */
            $roleRepository = $this->doctrineHelper->getEntityRepository($role);
            $this->appendUsers = $roleRepository->getAssignedUsers($role);
        }

        $this->loggedAccountUser = $this->securityFacade->getLoggedUser();

        /** @var EntityManager $manager */
        $manager = $this->managerRegistry->getManagerForClass(ClassUtils::getClass($this->loggedAccountUser));

        $connection = $manager->getConnection();
        $connection->setTransactionIsolation(Connection::TRANSACTION_REPEATABLE_READ);
        $connection->beginTransaction();

        try {
            $this->removeOriginalRoleFromUsers($role, $manager);
            AclRoleHandler::onSuccess($this->newRole, $appendUsers, $removeUsers);
            $this->addNewRoleToUsers($role, $manager, $appendUsers, $removeUsers);

            $manager->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param AccountUserRole|AbstractRole $role
     * @param EntityManager             $manager
     * @param array                        $appendUsers
     * @param array                        $removeUsers
     */
    protected function addNewRoleToUsers(
        AccountUserRole $role,
        EntityManager $manager,
        array $appendUsers,
        array $removeUsers
    ) {
        if (!$role->getId() || $role->getId() === $this->newRole->getId()) {
            return;
        }

        $accountRolesToAdd = array_diff($this->appendUsers, $removeUsers);
        $accountRolesToAdd = array_merge($accountRolesToAdd, $appendUsers);
        array_map(
            function (AccountUser $accountUser) use ($role, $manager) {
                if ($accountUser->getAccount()->getId() === $this->loggedAccountUser->getAccount()->getId()) {
                    $accountUser->addRole($this->newRole);
                    $manager->persist($accountUser);
                }
            },
            $accountRolesToAdd
        );
    }

    /**
     * @param AccountUserRole|AbstractRole $role
     * @param EntityManager                $manager
     */
    protected function removeOriginalRoleFromUsers(AccountUserRole $role, EntityManager $manager)
    {
        if (!$role->getId() || $role->getId() === $this->newRole->getId()) {
            return;
        }

        array_map(
            function (AccountUser $accountUser) use ($role, $manager) {
                if ($accountUser->getAccount()->getId() === $this->loggedAccountUser->getAccount()->getId()) {
                    $accountUser->removeRole($role);
                    $manager->persist($accountUser);
                }
            },
            $this->appendUsers
        );
    }

    /**
     * @param SecurityFacade $securityFacade
     */
    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(AbstractRole $role)
    {
        $this->newRole = $role;

        return parent::createForm($role);
    }

    /**
     * {@inheritdoc}
     */
    protected function processPrivileges(AbstractRole $role)
    {
        parent::processPrivileges($this->newRole);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRoleFormInstance(AbstractRole $role, array $privilegeConfig)
    {
        return $this->formFactory->create(
            FrontendAccountUserRoleType::NAME,
            $role,
            ['privilege_config' => $privilegeConfig]
        );
    }
}
