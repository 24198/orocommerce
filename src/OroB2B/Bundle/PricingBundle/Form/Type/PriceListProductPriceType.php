<?php

namespace OroB2B\Bundle\PricingBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\PricingBundle\Entity\ProductPrice;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductSelectType;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;
use OroB2B\Bundle\ProductBundle\Rounding\RoundingService;

class PriceListProductPriceType extends AbstractType
{
    const NAME = 'orob2b_pricing_price_list_product_price';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var RoundingService
     */
    protected $roundingService;

    /**
     * @var string
     */
    protected $productClass;

        /**
     * @param ManagerRegistry $registry
     * @param RoundingService $roundingService
     */
    public function __construct(ManagerRegistry $registry, RoundingService $roundingService)
    {
        $this->registry = $registry;
        $this->roundingService = $roundingService;
    }

    /**
     * @param string $productClass
     */
    public function setProductClass($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProductPrice $data */
        $data = $builder->getData();
        $isExisting = $data && $data->getId();

        $additionalCurrencies = [];
        if ($data->getPriceList()) {
            $additionalCurrencies = $data->getPriceList()->getCurrencies();
        }

        $builder
            ->add(
                'product',
                ProductSelectType::NAME,
                [
                    'required' => true,
                    'label' => 'orob2b.pricing.productprice.product.label',
                    'create_enabled' => false,
                    'disabled' => $isExisting
                ]
            )
            ->add(
                'quantity',
                'text',
                [
                    'required' => true,
                    'label' => 'orob2b.pricing.productprice.quantity.label'
                ]
            )
            ->add(
                'unit',
                ProductUnitSelectionType::NAME,
                [
                    'required' => true,
                    'label' => 'orob2b.pricing.productprice.unit.label',
                    'empty_data' => null,
                    'empty_value' => 'orob2b.pricing.productprice.unit.choose'
                ]
            )
            ->add(
                'price',
                PriceType::NAME,
                [
                    'required' => true,
                    'compact' => true,
                    'label' => 'orob2b.pricing.productprice.price.label',
                    'additional_currencies' => $additionalCurrencies
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmitData']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmitData(FormEvent $event)
    {
        $data = $event->getData();

        if (!isset($data['product'], $data['unit'], $data['quantity'])) {
            return;
        }

        /** @var Product $product */
        $product = $this->registry
            ->getRepository($this->productClass)
            ->find($data['product']);

        if ($product) {
            $unitPrecision = $product->getUnitPrecision($data['unit']);

            if ($unitPrecision) {
                $data['quantity'] = $this->roundingService->round($data['quantity'], $unitPrecision->getPrecision());

                $event->setData($data);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $productClass
     * @return PriceListProductPriceType
     */
    public function setDataClass($productClass)
    {
        $this->dataClass = $productClass;

        return $this;
    }
}
