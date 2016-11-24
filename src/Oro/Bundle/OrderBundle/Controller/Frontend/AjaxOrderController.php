<?php

namespace Oro\Bundle\OrderBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\OrderBundle\Controller\AjaxOrderController as BaseAjaxOrderController;
use Oro\Bundle\OrderBundle\RequestHandler\FrontendOrderDataHandler;
use Oro\Bundle\OrderBundle\Form\Type\FrontendOrderType;
use Oro\Bundle\OrderBundle\Entity\Order;

class AjaxOrderController extends BaseAjaxOrderController
{
    /**
     * @Route("/entry-point/{id}", name="oro_order_frontend_entry_point", defaults={"id" = 0})
     * @AclAncestor("oro_order_frontend_update")
     *
     * @param Request $request
     * @param Order|null $order
     * @return JsonResponse
     */
    public function entryPointAction(Request $request, Order $order = null)
    {
        if ($order) {
            $order->setAccountUser($this->getOrderHandler()->getAccountUser());
            $order->setAccount($this->getOrderHandler()->getAccount());
            $this->get('oro_payment_term.provider.payment_term_association')->setPaymentTerm(
                $order,
                $this->getOrderHandler()->getPaymentTerm()
            );
            $order->setOwner($this->getOrderHandler()->getOwner());
        }

        return parent::entryPointAction($request, $order);
    }

    /**
     * @param Order $order
     * @return Form
     */
    protected function getType(Order $order)
    {
        return $this->createForm(FrontendOrderType::NAME, $order);
    }

    /**
     * @return FrontendOrderDataHandler
     */
    protected function getOrderHandler()
    {
        return $this->get('oro_order.request_handler.frontend_order_data_handler');
    }
}
