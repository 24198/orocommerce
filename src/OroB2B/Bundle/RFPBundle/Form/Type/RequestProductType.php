<?php

namespace OroB2B\Bundle\RFPBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use OroB2B\Bundle\PricingBundle\Form\Type\ProductPriceListAwareSelectType;

class RequestProductType extends AbstractType
{
    const NAME = 'orob2b_rfp_request_product';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductPriceListAwareSelectType::NAME, [
                'required'  => true,
                'label'     => 'orob2b.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('requestProductItems', RequestProductItemCollectionType::NAME, [
                'label'     => 'orob2b.rfp.requestproductitem.entity_plural_label',
                'add_label' => 'orob2b.rfp.requestproductitem.add_label',
            ])
            ->add('comment', 'textarea', [
                'required'  => false,
                'label'     => 'orob2b.rfp.requestproduct.comment.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'intention'  => 'rfp_request_product',
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
