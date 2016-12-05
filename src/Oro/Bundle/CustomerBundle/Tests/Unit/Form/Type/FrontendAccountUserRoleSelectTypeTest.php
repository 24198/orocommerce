<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\AccountUser;
use Oro\Bundle\CustomerBundle\Entity\AccountUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\AccountUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\AccountUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendAccountUserRoleSelectType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class FrontendAccountUserRoleSelectTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;
    /**
     * @var FrontendAccountUserRoleSelectType
     */
    protected $formType;

    /** @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var $registry Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var QueryBuilder */
    protected $qb;

    /**
     * @var AclHelper $aclHelper
     */
    protected $aclHelper;

    protected function setUp()
    {
        $account = $this->createAccount(1, 'account');
        $organization = $this->createOrganization(1);
        $user = new AccountUser();
        $criteria = new Criteria();
        $user->setAccount($account);
        $user->setOrganization($organization);
        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityFacade->expects($this->any())->method('getLoggedUser')->willReturn($user);
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $repo AccountUserRoleRepository|\PHPUnit_Framework_MockObject_MockObject */
        $repo = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\Repository\AccountUserRoleRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->any())
            ->method('createQueryBuilder')
            ->with('account')
            ->willReturn($this->qb);
        /** @var $em ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
        $em = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->any())
            ->method('getRepository')
            ->with('Oro\Bundle\CustomerBundle\Entity\AccountUserRole')
            ->willReturn($repo);
        $this->qb->expects($this->any())
            ->method('addCriteria')
            ->with($criteria)
            ->willReturn($this->qb);
        $this->aclHelper = $this->createAclHelperMock();
        $this->registry->expects($this->any())->method('getManagerForClass')->willReturn($em);
        $this->formType = new FrontendAccountUserRoleSelectType(
            $this->securityFacade,
            $this->registry,
            $this->aclHelper
        );
        $this->formType->setRoleClass('Oro\Bundle\CustomerBundle\Entity\AccountUserRole');

        parent::setUp();
    }

    public function testGetRegistry()
    {
        $this->assertEquals($this->formType->getRegistry(), $this->registry);
    }

    public function testGetName()
    {
        $this->assertEquals(FrontendAccountUserRoleSelectType::NAME, $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals(AccountUserRoleSelectType::NAME, $this->formType->getParent());
    }

    public function testConfigureOptions()
    {
        /** @var $resolver OptionsResolver|\PHPUnit_Framework_MockObject_MockObject */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $qb = new ORMQueryBuilderLoader($this->qb);

        $resolver->expects($this->once())
            ->method('setNormalizer')
            ->with($this->isType('string'), $this->isInstanceOf('\Closure'))
            ->willReturnCallback(
                function ($type, $closure) use ($qb) {
                    $this->assertEquals('loader', $type);
                    $this->assertEquals(
                        $closure(),
                        $qb
                    );
                }
            );
        $this->formType->configureOptions($resolver);
    }

    public function testEmptyUser()
    {
        /** @var $securityFacade SecurityFacade|\PHPUnit_Framework_MockObject_MockObject */
        $securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
        $securityFacade->expects($this->once())->method('getLoggedUser')->willReturn(null);
        /** @var $resolver OptionsResolver|\PHPUnit_Framework_MockObject_MockObject */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $roleFormType = new FrontendAccountUserRoleSelectType($securityFacade, $this->registry, $this->aclHelper);
        $roleFormType->configureOptions($resolver);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Account
     */
    protected function createAccount($id, $name)
    {
        $account = $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Account', ['id' => $id]);
        $account->setName($name);

        return $account;
    }

    /**
     * @param int $id
     * @return Account
     */
    protected function createOrganization($id)
    {
        return $this->getEntity('Oro\Bundle\OrganizationBundle\Entity\Organization', ['id' => $id]);
    }

    /**
     * @return AccountUserRole[]
     */
    protected function getRoles()
    {
        return [
            1 => $this->getRole(1, 'test01'),
            2 => $this->getRole(2, 'test02'),
        ];
    }

    /**
     * @param int $id
     * @param string $label
     * @return AccountUserRole
     */
    protected function getRole($id, $label)
    {
        $role = new AccountUserRole($label);

        $reflection = new \ReflectionProperty(get_class($role), 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($role, $id);

        $role->setLabel($label);

        return $role;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAclHelperMock()
    {
        return $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
