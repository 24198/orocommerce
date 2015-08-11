<?php

namespace OroB2B\Bundle\OrderBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

use OroB2B\Bundle\OrderBundle\Entity\Order;
use OroB2B\Bundle\OrderBundle\Form\Type\FrontendOrderType;
use OroB2B\Bundle\OrderBundle\Provider\OrderAddressSecurityProvider;

class OrderController extends Controller
{
    /**
     * @Route("/", name="orob2b_order_frontend_index")
     * @Template("OroB2BOrderBundle:Order/Frontend:index.html.twig")
     * @Acl(
     *      id="orob2b_order_frontend_view",
     *      type="entity",
     *      class="OroB2BOrderBundle:Order",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orob2b_order.entity.order.class'),
        ];
    }

    /**
     * @Route("/view/{id}", name="orob2b_order_frontend_view", requirements={"id"="\d+"})
     * @Template("OroB2BOrderBundle:Order/Frontend:view.html.twig")
     * @AclAncestor("orob2b_order_frontend_view")
     *
     * @param Order $order
     * @return array
     */
    public function viewAction(Order $order)
    {
        return [
            'entity' => $order,
        ];
    }

    /**
     * @Route("/info/{id}", name="orob2b_order_frontend_info", requirements={"id"="\d+"})
     * @Template("OroB2BOrderBundle:Order/Frontend:info.html.twig")
     * @AclAncestor("orob2b_order_frontend_view")
     *
     * @param Order $order
     * @return array
     */
    public function infoAction(Order $order)
    {
        return [
            'order' => $order,
        ];
    }

    /**
     * Create order form
     *
     * @Route("/create", name="orob2b_order_frontend_create")
     * @Template("OroB2BOrderBundle:Order/Frontend:update.html.twig")
     * @Acl(
     *      id="orob2b_order_frontend_create",
     *      type="entity",
     *      class="OroB2BOrderBundle:Order",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     *
     * @return array|RedirectResponse
     */
    public function createAction()
    {
        return $this->update(new Order());
    }

    /**
     * Edit order form
     *
     * @Route("/update/{id}", name="orob2b_order_frontend_update", requirements={"id"="\d+"})
     * @Template("OroB2BOrderBundle:Order/Frontend:update.html.twig")
     * @Acl(
     *      id="orob2b_order_frontend_update",
     *      type="entity",
     *      class="OroB2BOrderBundle:Order",
     *      permission="EDIT",
     *      group_name="commerce"
     * )
     *
     * @param Order $order
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Order $order)
    {
        return $this->update($order);
    }

    /**
     * @param Order $order
     *
     * @return array|RedirectResponse
     */
    protected function update(Order $order)
    {
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $order,
            $this->createForm(FrontendOrderType::NAME, $order),
            function (Order $order) {
                return [
                    'route' => 'orob2b_order_frontend_update',
                    'parameters' => ['id' => $order->getId()],
                ];
            },
            function (Order $order) {
                return [
                    'route' => 'orob2b_order_frontend_view',
                    'parameters' => ['id' => $order->getId()],
                ];
            },
            $this->get('translator')->trans('orob2b.order.controller.order.saved.message'),
            null,
            function (Order $order, FormInterface $form, Request $request) {
                return [
                    'form' => $form->createView(),
                    'entity' => $order,
                    'isWidgetContext' => (bool)$request->get('_wid', false),
                    'isShippingAddressGranted' => $this->getOrderAddressSecurityProvider()
                        ->isAddressGranted($order, AddressType::TYPE_SHIPPING),
                    'isBillingAddressGranted' => $this->getOrderAddressSecurityProvider()
                        ->isAddressGranted($order, AddressType::TYPE_BILLING),
                ];
            }
        );
    }

    /**
     * @return OrderAddressSecurityProvider
     */
    protected function getOrderAddressSecurityProvider()
    {
        return $this->get('orob2b_order.order.provider.order_address_security');
    }
}
