<?php

namespace OroB2B\Bundle\CatalogBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use OroB2B\Bundle\CatalogBundle\Entity\Category;

class ProductController extends Controller
{
    /**
     * @Route("/sidebar", name="orob2b_catalog_category_product_sidebar")
     * @Template
     *
     * @param Request $request
     * @return array
     */
    public function sidebarAction(Request $request)
    {
        if ($request->query->has('categoryId')) {
            $defaultCategoryId = $request->query->get('categoryId');
        } else {
            /** @var Category $rootCategory */
            $rootCategory = $this->getDoctrine()->getRepository('OroB2BCatalogBundle:Category')->getMasterCatalogRoot();
            $defaultCategoryId = $rootCategory->getId();
        }

        return ['defaultCategoryId' => $defaultCategoryId];
    }
}
