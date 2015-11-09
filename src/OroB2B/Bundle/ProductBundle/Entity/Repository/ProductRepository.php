<?php

namespace OroB2B\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use OroB2B\Bundle\ProductBundle\Entity\Product;

class ProductRepository extends EntityRepository
{
    /**
     * @param string $sku
     *
     * @return null|Product
     */
    public function findOneBySku($sku)
    {
        return $this->findOneBy(['sku' => $sku]);
    }

    /**
     * @param string $pattern
     *
     * @return string[]
     */
    public function findAllSkuByPattern($pattern)
    {
        $matchedSku = [];

        $results = $this
            ->createQueryBuilder('product')
            ->select('product.sku')
            ->where('product.sku LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->getQuery()
            ->getResult();

        foreach ($results as $result) {
            $matchedSku[] = $result['sku'];
        }

        return $matchedSku;
    }

    /**
     * @param array $productIds
     *
     * @return QueryBuilder
     */
    public function getProductsQueryBuilder(array $productIds = [])
    {
        $productsQueryBuilder = $this
            ->createQueryBuilder('p')
            ->select('p');

        if (count($productIds) > 0) {
            $productsQueryBuilder
                ->where($productsQueryBuilder->expr()->in('p', ':product_ids'))
                ->setParameter('product_ids', $productIds);
        }

        return $productsQueryBuilder;
    }

    /**
     * @param array $productSkus
     *
     * @return array Ids
     */
    public function getProductsIdsBySku(array $productSkus = [])
    {
        $productsQueryBuilder = $this
            ->createQueryBuilder('p')
            ->select('p.id, p.sku');

        if ($productSkus) {
            $productsQueryBuilder
                ->where($productsQueryBuilder->expr()->in('p.sku', ':product_skus'))
                ->setParameter('product_skus', $productSkus);
        }

        $productsData = $productsQueryBuilder
            ->orderBy($productsQueryBuilder->expr()->asc('p.id'))
            ->getQuery()
            ->getArrayResult();

        $productsIdsToSku = [];
        foreach ($productsData as $key => $productData) {
            $productsIdsToSku[$productData['sku']] = $productData['id'];
            unset($productsData[$key]);
        }

        return $productsIdsToSku;
    }

    /**
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @return QueryBuilder
     */
    public function getSearchQueryBuilder($search, $firstResult, $maxResults)
    {
        $productsQueryBuilder = $this
            ->createQueryBuilder('p');

        $productsQueryBuilder->innerJoin('p.names', 'pn', 'WITH', $productsQueryBuilder->expr()->isNull('pn.locale'))
            ->where(
                $productsQueryBuilder->expr()->orX(
                    $productsQueryBuilder->expr()->like('LOWER(p.sku)', ':search'),
                    $productsQueryBuilder->expr()->like('LOWER(pn.string)', ':search')
                )
            )
            ->setParameter('search', '%' . strtolower($search) . '%')
            ->addOrderBy('p.id')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        return $productsQueryBuilder;
    }
}
