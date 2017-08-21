<?php

namespace Oro\Bundle\RFPBundle\Form\Type\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\CustomerBundle\Form\Type\Frontend\CustomerUserMultiSelectType;

class RequestType extends AbstractType
{
    const NAME = 'oro_rfp_frontend_request';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param ConfigManager $configManager
     * @param ManagerRegistry $registry
     */
    public function __construct(ConfigManager $configManager, ManagerRegistry $registry)
    {
        $this->configManager = $configManager;
        $this->registry = $registry;
    }

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
            ->add('firstName', 'text', [
                'label' => 'oro.rfp.request.first_name.label'
            ])
            ->add('lastName', 'text', [
                'label' => 'oro.rfp.request.last_name.label'
            ])
            ->add('email', 'text', [
                'label' => 'oro.rfp.request.email.label'
            ])
            ->add('phone', 'text', [
                'required' => false,
                'label' => 'oro.rfp.request.phone.label'
            ])
            ->add('company', 'text', [
                'label' => 'oro.rfp.request.company.label'
            ])
            ->add('role', 'text', [
                'required' => false,
                'label' => 'oro.rfp.request.role.label'
            ])
            ->add('note', 'textarea', [
                'required' => false,
                'label' => 'oro.rfp.request.note.label'
            ])
            ->add('poNumber', 'text', [
                'required' => false,
                'label' => 'oro.rfp.request.po_number.label'
            ])
            ->add('shipUntil', OroDateType::NAME, [
                'required' => false,
                'label' => 'oro.rfp.request.ship_until.label'
            ])
            ->add('requestProducts', RequestProductCollectionType::NAME, [
                'options' => [
                    'compact_units' => true,
                ],
            ])
            ->add('assignedCustomerUsers', CustomerUserMultiSelectType::NAME, [
                'label' => 'oro.frontend.rfp.request.assigned_customer_users.label',
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
            'attr'=> array(
                'class'=>'frontend_edit_form'
            )
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }
}
