<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Type\AccountUserTypedAddressType;

class AccountUserAddressController extends Controller
{
    /**
     * @Route("/address-book/{id}", name="oro_customer_account_user_address_book", requirements={"id"="\d+"})
     * @Template("OroCustomerBundle:Address/widget:addressBook.html.twig")
     * @AclAncestor("oro_account_account_user_view")
     *
     * @param CustomerUser $accountUser
     * @return array
     */
    public function addressBookAction(CustomerUser $accountUser)
    {
        return [
            'entity' => $accountUser,
            'address_edit_acl_resource' => 'oro_account_account_user_update',
            'options' => $this->getAddressBookOptions($accountUser)
        ];
    }

    /**
     * @Route(
     *      "/{entityId}/address-create",
     *      name="oro_customer_account_user_address_create",
     *      requirements={"accountUserId"="\d+"}
     * )
     * @Template("OroCustomerBundle:Address/widget:update.html.twig")
     * @AclAncestor("oro_account_account_user_create")
     * @ParamConverter("accountUser", options={"id" = "entityId"})
     *
     * @param CustomerUser $accountUser
     * @return array
     */
    public function createAction(CustomerUser $accountUser)
    {
        return $this->update($accountUser, new CustomerUserAddress());
    }

    /**
     * @Route(
     *      "/{entityId}/address-update/{id}",
     *      name="oro_customer_account_user_address_update",
     *      requirements={"accountUserId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template("OroCustomerBundle:Address/widget:update.html.twig")
     * @AclAncestor("oro_account_account_user_update")
     * @ParamConverter("accountUser", options={"id" = "entityId"})
     *
     * @param CustomerUser        $accountUser
     * @param CustomerUserAddress $address
     * @return array
     */
    public function updateAction(CustomerUser $accountUser, CustomerUserAddress $address)
    {
        return $this->update($accountUser, $address);
    }

    /**
     * @param CustomerUser $accountUser
     * @param CustomerUserAddress $address
     * @return array
     * @throws BadRequestHttpException
     */
    protected function update(CustomerUser $accountUser, CustomerUserAddress $address)
    {
        $responseData = [
            'saved' => false,
            'entity' => $accountUser
        ];

        if ($this->getRequest()->getMethod() === 'GET' && !$address->getId()) {
            $address->setFirstName($accountUser->getFirstName());
            $address->setLastName($accountUser->getLastName());
            if (!$accountUser->getAddresses()->count()) {
                $address->setPrimary(true);
            }
        }

        if (!$address->getFrontendOwner()) {
            $accountUser->addAddress($address);
        } elseif ($address->getFrontendOwner()->getId() !== $accountUser->getId()) {
            throw new BadRequestHttpException('Address must belong to CustomerUser');
        }

        $form = $this->createForm(AccountUserTypedAddressType::NAME, $address);

        $manager = $this->getDoctrine()->getManagerForClass(
            $this->container->getParameter('oro_customer.entity.account_user_address.class')
        );

        $handler = new AddressHandler($form, $this->getRequest(), $manager);

        if ($handler->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        $responseData['routes'] = [
            'create' => 'oro_customer_account_user_address_create',
            'update' => 'oro_customer_account_user_address_update'
        ];
        return $responseData;
    }

    /**
     * @param CustomerUser $entity
     * @return array
     */
    protected function getAddressBookOptions($entity)
    {
        $addressListUrl = $this->generateUrl('oro_api_account_get_accountuser_addresses', [
            'entityId' => $entity->getId()
        ]);

        $addressCreateUrl = $this->generateUrl('oro_customer_account_user_address_create', [
            'entityId' => $entity->getId()
        ]);

        $currentAddresses = $this->get('fragment.handler')->render($addressListUrl);

        return [
            'wid'                    => $this->getRequest()->get('_wid'),
            'entityId'               => $entity->getId(),
            'addressListUrl'         => $addressListUrl,
            'addressCreateUrl'       => $addressCreateUrl,
            'addressUpdateRouteName' => 'oro_customer_account_user_address_update',
            'currentAddresses'       => $currentAddresses
        ];
    }
}
