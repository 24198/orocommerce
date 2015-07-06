<?php

namespace OroB2B\Bundle\RFPAdminBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use OroB2B\Bundle\RFPAdminBundle\Entity\RequestProductItem as RequestProductItemEntity;

class RequestProductItemValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @param RequestProductItemEntity $requestProductItem
     * @param RequestProductItem $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($requestProductItem, Constraint $constraint)
    {
        if (!$requestProductItem instanceof RequestProductItemEntity) {
            throw new UnexpectedTypeException(
                $requestProductItem,
                'OroB2B\Bundle\RFPAdminBundle\Entity\RequestProductItem'
            );
        }

        $requestProduct = $requestProductItem->getRequestProduct();
        $product        = $requestProduct ? $requestProduct->getProduct() : null;
        $allowedUnits   = $product ? $product->getAvailableUnitCodes() : [];
        $productUnit    = $requestProductItem->getProductUnit();
        $code           = $productUnit ? $productUnit->getCode() : null;

        if (!in_array($code, $allowedUnits, true)) {
            $this->context->addViolationAt('productUnit', $constraint->message);
        }
    }
}
