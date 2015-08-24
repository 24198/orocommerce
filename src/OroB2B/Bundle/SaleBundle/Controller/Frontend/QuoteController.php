<?php

namespace OroB2B\Bundle\SaleBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroB2B\Bundle\SaleBundle\Entity\Quote;
use OroB2B\Bundle\SaleBundle\Form\Handler\QuoteCreateOrderHandler;

class QuoteController extends Controller
{
    /**
     * @Route("/view/{id}", name="orob2b_sale_frontend_quote_view", requirements={"id"="\d+"})
     * @Template("OroB2BSaleBundle:Quote/Frontend:view.html.twig")
     * @param Quote $quote
     * @return array
     */
    public function viewAction(Quote $quote)
    {
        return [
            'entity' => $quote,
            'formCreateOrder' => $this->getCreateOrderForm($quote)->createView(),
        ];
    }

    /**
     * @Route("/", name="orob2b_sale_frontend_quote_index")
     * @Template("OroB2BSaleBundle:Quote/Frontend:index.html.twig")
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orob2b_sale.entity.quote.class')
        ];
    }

    /**
     * @Route("/info/{id}", name="orob2b_sale_frontend_quote_info", requirements={"id"="\d+"})
     * @Template("OroB2BSaleBundle:Quote/Frontend/widget:info.html.twig")
     *
     * @param Quote $quote
     * @return array
     */
    public function infoAction(Quote $quote)
    {
        return [
            'entity' => $quote
        ];
    }

    /**
     * @Route("/create_order/{id}", name="orob2b_sale_frontend_quote_create_order", requirements={"id"="\d+"})
     * @Acl(
     *      id="orob2b_sale_frontend_quote_create_order",
     *      type="entity",
     *      class="OroB2BSaleBundle:Quote",
     *      permission="VIEW"
     * )
     * @Template("OroB2BSaleBundle:Quote/Frontend:view.html.twig")
     * @param Request $request
     * @param Quote $quote
     * @return RedirectResponse
     */
    public function createQuoteAction(Request $request, Quote $quote)
    {
        return $this->createOrder($request, $quote);
    }

    /**
     * @param Request $request
     * @param Quote $quote
     * @return array|RedirectResponse
     */
    protected function createOrder(Request $request, Quote $quote)
    {
        $form = $this->getCreateOrderForm($quote);
        $handler = new QuoteCreateOrderHandler(
            $form,
            $request,
            $this->getDoctrine()->getManagerForClass('OroB2BPricingBundle:PriceList'),
            $this->getUser()
        );

        return $this->get('oro_form.model.update_handler')
            ->handleUpdate(
                $quote,
                $form,
                function (Quote $quote) {
                    return [
                        'route'         => 'orob2b_sale_frontend_quote_view',
                        'parameters'    => ['id' => $quote->getId()],
                    ];
                },
                function () use ($handler) {
                    return [
                        'route'         => 'orob2b_order_frontend_order_view',
                        'parameters'    => ['id' => $handler->getOrder()->getId()],
                    ];
                },
                $this->getTranslator()->trans('orob2b.frontend.sale.message.quote.create_order.success'),
                $handler,
                function (Quote $entity, FormInterface $form) use ($handler) {
                    /* @var $session Session */
                    $session = $this->get('session');
                    $session->getFlashBag()->add(
                        'error',
                        $this->getTranslator()->trans('orob2b.frontend.sale.message.quote.create_order.error')
                    );

                    if ($handler->getException()) {
                        $this->getLogger()->error($handler->getException());
                    }

                    return [
                        'entity' => $entity,
                        'formCreateOrder' => $form->createView(),
                    ];
                }
            );
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->get('logger');
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @param Quote $quote
     * @return Form
     */
    protected function getCreateOrderForm(Quote $quote = null)
    {
        return $this->createFormBuilder($quote)->getForm();
    }
}
