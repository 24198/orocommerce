<?php

namespace Oro\Bundle\ShoppingListBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class MatrixGridOrderController extends AbstractLineItemController
{
    /**
     * @Route("/{productId}", name="oro_shopping_list_frontend_matrix_grid_order")
     * @ParamConverter("product", options={"id" = "productId"})
     * @Layout()
     * @Acl(
     *      id="oro_shopping_list_frontend_view",
     *      type="entity",
     *      class="OroShoppingListBundle:ShoppingList",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     *
     * @param Request $request
     * @param Product $product
     * @return array | JsonResponse
     */
    public function orderAction(Request $request, Product $product)
    {
        $matrixOrderFormProvider = $this->get('oro_shopping_list.layout.data_provider.matrix_order_form');
        $matrixGridOrderManager = $this->get('oro_shopping_list.provider.matrix_grid_order_manager');

        $shoppingListManager = $this->get('oro_shopping_list.shopping_list.manager');
        $shoppingList = $shoppingListManager->getForCurrentUser($request->get('shoppingListId'));

        $form = $matrixOrderFormProvider->getMatrixOrderForm($product, $shoppingList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lineItems = $matrixGridOrderManager->convertMatrixIntoLineItems(
                $form->getData(),
                $product,
                $request->request->get('matrix_collection', [])
            );

            $updateQuantity = (bool) $request->get('updateQuantity', false);
            foreach ($lineItems as $lineItem) {
                if ($updateQuantity === false) {
                    $shoppingListManager->addLineItem($lineItem, $shoppingList, true, true);
                } else {
                    $shoppingListManager->updateLineItem($lineItem, $shoppingList);
                }
            }

            if ($lineItems) {
                $message = $this->getSuccessMessage($shoppingList, 'oro.shoppinglist.product.added.label');
                $this->get('session')->getFlashBag()->add('success', $message);
            }

            if ($request->isXmlHttpRequest()) {
                $url = $this->generateUrl(
                    'oro_product_frontend_product_view',
                    ['id' => $product->getId()]
                );

                return new JsonResponse(['redirectUrl' => $url]);
            }
        }

        $products = $this->get('oro_product.provider.product_variant_availability_provider')
            ->getSimpleProductsByVariantFields($product);

        return ['data' => ['product' => $product, 'products' => ['data' => $products]]];
    }
}
