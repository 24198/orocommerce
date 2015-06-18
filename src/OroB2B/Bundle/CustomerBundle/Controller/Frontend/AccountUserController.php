<?php

namespace OroB2B\Bundle\CustomerBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\CustomerBundle\Form\Type\FrontendAccountUserType;
use OroB2B\Bundle\CustomerBundle\Form\Handler\FrontendAccountUserHandler;
use OroB2B\Bundle\WebsiteBundle\Manager\WebsiteManager;
use OroB2B\Bundle\CustomerBundle\Form\Type\FrontendAccountUserRegistrationType;

class AccountUserController extends Controller
{
    /**
     * Create account user form
     *
     * @Route("/register", name="orob2b_customer_frontend_account_user_register")
     * @Template("OroB2BCustomerBundle:AccountUser/Frontend:register.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function registerAction()
    {
        $isRegistrationAllowed = $this->get('oro_config.manager')->get('oro_b2b_customer.registration_allowed');
        if (!$isRegistrationAllowed) {
            throw new AccessDeniedException();
        }

        $accountUser = new AccountUser();

        /** @var WebsiteManager $websiteManager */
        $websiteManager = $this->get('orob2b_website.manager');
        $website = $websiteManager->getCurrentWebsite();
        $websiteOrganization = $website->getOrganization();

        if (!$websiteOrganization) {
            throw new \RuntimeException('Website organization is empty');
        }

        $defaultRole = $this->getDoctrine()
            ->getManagerForClass('OroB2BCustomerBundle:AccountUserRole')
            ->getRepository('OroB2BCustomerBundle:AccountUserRole')
            ->findOneBy(['role' => $accountUser->getDefaultRole()]);

        if (!$defaultRole) {
            throw new \RuntimeException(sprintf('Role "%s" was not found', $accountUser->getDefaultRole()));
        }

        $accountUser
            ->addOrganization($websiteOrganization)
            ->setOrganization($websiteOrganization)
            ->addRole($defaultRole);

        $form = $this->createForm(FrontendAccountUserRegistrationType::NAME, $accountUser);
        $handler = new FrontendAccountUserHandler(
            $form,
            $this->getRequest(),
            $this->get('orob2b_account_user.manager')
        );

        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $accountUser,
            $form,
            ['route' => 'orob2b_customer_account_user_security_login'],
            ['route' => 'orob2b_customer_account_user_security_login'],
            $this->get('translator')->trans('orob2b.customer.controller.accountuser.registered.message'),
            $handler
        );
    }

    /**
     * @Route("/profile", name="orob2b_customer_frontend_account_user_profile")
     * @Template("OroB2BCustomerBundle:AccountUser/Frontend:view.html.twig")
     * @Acl(
     *      id="orob2b_customer_frontend_account_user_profile",
     *      type="entity",
     *      class="OroB2BCustomerBundle:AccountUser",
     *      permission="VIEW"
     * )
     *
     * @return array
     */
    public function profileAction()
    {
        return [
            'entity' => $this->getUser(),
            'editRoute' => 'orob2b_customer_frontend_account_user_profile_update'
        ];
    }

    /**
     * Edit account user form
     *
     * @Route("/profile/update", name="orob2b_customer_frontend_account_user_profile_update")
     * @Template("OroB2BCustomerBundle:AccountUser/Frontend:update.html.twig")
     * @Acl(
     *      id="orob2b_customer_frontend_account_user_profile_update",
     *      type="entity",
     *      class="OroB2BCustomerBundle:AccountUser",
     *      permission="EDIT"
     * )
     *
     * @return array|RedirectResponse
     */
    public function updateAction()
    {
        $accountUser = $this->getUser();

        $form = $this->createForm(FrontendAccountUserType::NAME, $accountUser);
        $handler = new FrontendAccountUserHandler(
            $form,
            $this->getRequest(),
            $this->get('orob2b_account_user.manager')
        );

        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $accountUser,
            $form,
            ['route' => 'orob2b_customer_frontend_account_user_profile_update'],
            ['route' => 'orob2b_customer_frontend_account_user_profile'],
            $this->get('translator')->trans('orob2b.customer.controller.accountuser.profile_updated.message'),
            $handler
        );
    }
}
