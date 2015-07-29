<?php

namespace OroB2B\Bundle\ShoppingListBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ShoppingListBundle\Entity\LineItem;
use OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList;
use OroB2B\Bundle\ShoppingListBundle\Form\Handler\LineItemHandler;
use OroB2B\Bundle\ShoppingListBundle\Form\Type\AddProductType;
use OroB2B\Bundle\ShoppingListBundle\Form\Type\FrontendLineItemType;

class AjaxLineItemController extends Controller
{
    /**
     * Add Product to shopping list (create line item) form
     *
     * @Route(
     *      "/add-product/{productId}",
     *      name="orob2b_shopping_list_line_item_frontend_add_widget",
     *      requirements={"productId"="\d+"}
     * )
     * @Template("OroB2BShoppingListBundle:LineItem/Frontend/widget:add.html.twig")
     * @AclAncestor("orob2b_shoppinglist_add_product")
     * @ParamConverter("product", class="OroB2BProductBundle:Product", options={"id" = "productId"})
     *
     * @param Product $product
     *
     * @return array|RedirectResponse
     */
    public function addProductAction(Product $product)
    {
        $lineItem = new LineItem();
        $lineItem->setProduct($product);

        $form = $this->createForm(AddProductType::NAME, $lineItem);
        $request = $this->getRequest();

        $handler = new LineItemHandler($form, $request, $this->getDoctrine());
        $result = $this->get('oro_form.model.update_handler')
            ->handleUpdate($lineItem, $form, null, null, null, $handler);

        if ($request->get('_wid')) {
            $result = $handler->updateSavedId($result);
        }

        return $result;
    }

    /**
     * Add Product to existing shopping list (create line item) form
     *
     * @Route(
     *      "/{shoppingListId}/products/{productId}",
     *      name="orob2b_shopping_list_frontend_add_product",
     *      requirements={"shoppingListId"="\d+", "productId"="\d+"}
     * )
     * @Acl(
     *      id="orob2b_shopping_list_line_item_frontend_add",
     *      type="entity",
     *      class="OroB2BShoppingListBundle:ShoppingList",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     * @ParamConverter("shoppingList", class="OroB2BShoppingListBundle:ShoppingList", options={"id" = "shoppingListId"})
     * @ParamConverter("product", class="OroB2BProductBundle:Product", options={"id" = "productId"})
     *
     * @param ShoppingList $shoppingList
     * @param Product $product
     *
     * @return array|RedirectResponse
     */
    public function addNewProductAction(ShoppingList $shoppingList, Product $product)
    {
        $lineItem = new LineItem();
        $lineItem->setProduct($product);
        $lineItem->setShoppingList($shoppingList);

        return $this->update($lineItem);
    }

    /**
     * @param LineItem $lineItem
     *
     * @return array|RedirectResponse
     */
    protected function update(LineItem $lineItem)
    {
        $form = $this->createForm(FrontendLineItemType::NAME, $lineItem);

        $isFormHandled = $this->get('orob2b_shopping_list.form.handler.frontend_line_item')->handle($form, $lineItem);

        if (!$isFormHandled) {
            return new JsonResponse(['successful' => false, 'message' => $form->getErrorsAsString()]);
        }

        $message = $this->get('translator')->trans('orob2b.shoppinglist.line_item_save.flash.success', [], 'jsmessages');

        return new JsonResponse(['successful' => true, 'message' => $message]);
    }
}
