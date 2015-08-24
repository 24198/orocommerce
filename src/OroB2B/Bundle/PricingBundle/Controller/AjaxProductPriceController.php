<?php

namespace OroB2B\Bundle\PricingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\PricingBundle\Entity\ProductPrice;
use OroB2B\Bundle\PricingBundle\Form\Type\PriceListProductPriceType;

class AjaxProductPriceController extends AbstractAjaxProductPriceController
{
    /**
     * Create product form
     *
     * @Route(
     *      "/create/{priceListId}",
     *      name="orob2b_product_price_create_widget",
     *      requirements={"priceListId"="\d+"}
     * )
     * @Template("OroB2BPricingBundle:ProductPrice:widget/update.html.twig")
     * @Acl(
     *      id="orob2b_pricing_product_price_create",
     *      type="entity",
     *      class="OroB2BPricingBundle:ProductPrice",
     *      permission="CREATE"
     * )
     * @ParamConverter("priceList", class="OroB2BPricingBundle:PriceList", options={"id" = "priceListId"})
     *
     * @param PriceList $priceList
     * @return array|RedirectResponse
     */
    public function createAction(PriceList $priceList)
    {
        $productPrice = new ProductPrice();
        $productPrice->setPriceList($priceList);

        return $this->update($productPrice);
    }

    /**
     * Edit product form
     *
     * @Route("/update/{id}", name="orob2b_product_price_update_widget", requirements={"id"="\d+"})
     * @Template("OroB2BPricingBundle:ProductPrice:widget/update.html.twig")
     * @Acl(
     *      id="orob2b_pricing_product_price_update",
     *      type="entity",
     *      class="OroB2BPricingBundle:ProductPrice",
     *      permission="EDIT"
     * )
     * @param ProductPrice $productPrice
     * @return array|RedirectResponse
     */
    public function updateAction(ProductPrice $productPrice)
    {
        return $this->update($productPrice);
    }

    /**
     * @Route("/get-product-prices-by-pricelist", name="orob2b_product_price_by_pricelist")
     * @Method({"GET"})
     * @AclAncestor("orob2b_pricing_product_price_view")
     *
     * {@inheritdoc}
     */
    public function getProductPricesByPriceListAction(Request $request)
    {
        return parent::getProductPricesByPriceListAction($request);
    }

    /**
     * @param ProductPrice $productPrice
     * @return array|RedirectResponse
     */
    protected function update(ProductPrice $productPrice)
    {
        $form = $this->createForm(PriceListProductPriceType::NAME, $productPrice);

        return $this->get('oro_form.model.update_handler')
            ->handleUpdate($productPrice, $form, null, null, null);
    }
}
