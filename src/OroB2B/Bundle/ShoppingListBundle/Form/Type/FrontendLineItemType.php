<?php

namespace OroB2B\Bundle\ShoppingListBundle\Form\Type;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use OroB2B\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;
use OroB2B\Bundle\ProductBundle\Entity\Repository\ProductUnitRepository;
use OroB2B\Bundle\ShoppingListBundle\Entity\LineItem;
use OroB2B\Bundle\ShoppingListBundle\EventListener\Form\Type\LineItemSubscriber;

class FrontendLineItemType extends AbstractType
{
    const NAME = 'orob2b_shopping_list_frontend_line_item';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var LineItemSubscriber
     */
    protected $lineItemSubscriber;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var LineItem $formData */
        $formData = $builder->getForm()->getData();
        $product = $formData->getProduct();

        $builder
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
                    'query_builder' => function (ProductUnitRepository $repository) use ($product) {
                        return $repository->getProductUnitsQueryBuilder($product);
                    }
                ]
            );

        $builder->addEventSubscriber($this->lineItemSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'validation_groups' => ['add_product']
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
     * @param string $dataClass
     *
     * @return $this
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * @param LineItemSubscriber $lineItemSubscriber
     */
    public function setLineItemSubscriber(LineItemSubscriber $lineItemSubscriber)
    {
        $this->lineItemSubscriber = $lineItemSubscriber;
    }
}
