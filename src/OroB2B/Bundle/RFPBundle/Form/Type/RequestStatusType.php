<?php

namespace OroB2B\Bundle\RFPBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class RequestStatusType extends AbstractType
{
    const NAME = 'orob2b_rfp_request_status';

    /**
     * @var ConfigManager
     */
    private $userConfig;

    /**
     * @var LocaleSettings
     */
    private $localeSettings;

    /**
     * @param ConfigManager $userConfig
     * @param LocaleSettings    $localeSettings
     */
    public function __construct(ConfigManager $userConfig, LocaleSettings $localeSettings)
    {
        $this->userConfig     = $userConfig;
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'label'    => 'orob2b.rfp.requeststatus.name.label',
                'required' => true
            ]
        )->add(
            'sortOrder',
            'integer',
            [
                'label'    => 'orob2b.rfp.requeststatus.sort_order.label',
                'required' => false
            ]
        );

        $lang              = $this->localeSettings->getLanguage();
        $notificationLangs = $this->userConfig->get('oro_locale.languages');
        $notificationLangs = array_unique(array_merge($notificationLangs, [$lang]));
        $localeLabels      = $this->localeSettings->getLocalesByCodes($notificationLangs, $lang);

        $builder->add(
            'translations',
            'orob2b_rfp_request_status_translation',
            [
                'label'    => 'orob2b.rfp.requeststatus.label.label',
                'required' => false,
                'locales'  => $notificationLangs,
                'labels'   => $localeLabels,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'OroB2B\\Bundle\\RFPBundle\\Entity\\RequestStatus',
                'intention'            => 'request_status',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
