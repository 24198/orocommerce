<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

class AccountAddressController extends Controller
{
    /**
     * @Route(
     *     "/{entityId}/create",
     *     name="oro_customer_frontend_account_address_create",
     *     requirements={"entityId":"\d+"}
     * )
     * @Acl(
     *      id="oro_account_frontend_account_address_create",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerAddress",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     * @Layout
     *
     * @ParamConverter("account", options={"id" = "entityId"})
     *
     * @param Account $account
     * @param Request $request
     * @return array
     */
    public function createAction(Account $account, Request $request)
    {
        return $this->update($account, new CustomerAddress(), $request);
    }

    /**
     * @Route(
     *     "/{entityId}/update/{id}",
     *     name="oro_customer_frontend_account_address_update",
     *     requirements={"entityId":"\d+", "id":"\d+"}
     * )
     * @Acl(
     *      id="oro_account_frontend_account_address_update",
     *      type="entity",
     *      class="OroCustomerBundle:CustomerAddress",
     *      permission="EDIT",
     *      group_name="commerce"
     * )
     * @Layout
     *
     * @ParamConverter("account", options={"id" = "entityId"})
     * @ParamConverter("accountAddress", options={"id" = "id"})
     *
     * @param Account $account
     * @param CustomerAddress $accountAddress
     * @param Request $request
     * @return array
     */
    public function updateAction(Account $account, CustomerAddress $accountAddress, Request $request)
    {
        return $this->update($account, $accountAddress, $request);
    }

    /**
     * @param Account $account
     * @param CustomerAddress $accountAddress
     * @param Request $request
     * @return array
     */
    private function update(Account $account, CustomerAddress $accountAddress, Request $request)
    {
        $this->prepareEntities($account, $accountAddress, $request);

        $form = $this->get('oro_customer.provider.frontend_account_address_form')
            ->getAddressForm($accountAddress, $account);

        $manager = $this->getDoctrine()->getManagerForClass(
            $this->container->getParameter('oro_customer.entity.account_address.class')
        );

        $handler = new AddressHandler($form, $request, $manager);

        $result = $this->get('oro_form.model.update_handler')->handleUpdate(
            $form->getData(),
            $form,
            function (CustomerAddress $accountAddress) use ($account) {
                return [
                    'route' => 'oro_customer_frontend_account_address_update',
                    'parameters' => ['id' => $accountAddress->getId(), 'entityId' => $account->getId()],
                ];
            },
            function (CustomerAddress $accountAddress) {
                return [
                    'route' => 'oro_customer_frontend_account_user_address_index'
                ];
            },
            $this->get('translator')->trans('oro.customer.controller.accountaddress.saved.message'),
            $handler,
            function (CustomerAddress $accountAddress, FormInterface $form, Request $request) {
                $url = $request->getUri();
                if ($request->headers->get('referer')) {
                    $url = $request->headers->get('referer');
                }

                return [
                    'backToUrl' => $url
                ];
            }
        );

        if ($result instanceof Response) {
            return $result;
        }

        return [
            'data' => array_merge($result, ['account' => $account])
        ];
    }

    /**
     * @param Account $account
     * @param CustomerAddress $accountAddress
     * @param Request $request
     */
    private function prepareEntities(Account $account, CustomerAddress $accountAddress, Request $request)
    {
        if ($request->getMethod() === 'GET' && !$accountAddress->getId()) {
            $accountAddress->setFirstName($account->getOwner()->getFirstName());
            $accountAddress->setLastName($account->getOwner()->getLastName());
            if (!$account->getAddresses()->count()) {
                $accountAddress->setPrimary(true);
            }
        }

        if (!$accountAddress->getFrontendOwner()) {
            $account->addAddress($accountAddress);
        } elseif ($accountAddress->getFrontendOwner()->getId() !== $account->getId()) {
            throw new BadRequestHttpException('Address must belong to Account');
        }
    }
}
