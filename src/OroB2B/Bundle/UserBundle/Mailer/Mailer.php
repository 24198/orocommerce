<?php

namespace OroB2B\Bundle\UserBundle\Mailer;

use FOS\UserBundle\Mailer\Mailer as BaseMailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\ApplicationBundle\Config\ConfigManager;

class Mailer extends BaseMailer
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param mixed $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param array $parameters
     * @param ConfigManager $configManager
     */
    public function __construct(
        $mailer,
        RouterInterface $router,
        EngineInterface $templating,
        array $parameters,
        ConfigManager $configManager
    ) {
        parent::__construct($mailer, $router, $templating, $parameters);

        $this->configManager = $configManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $email = $this->configManager->get('oro_notification.email_notification_sender_email');
        $name = $this->configManager->get('oro_notification.email_notification_sender_name');

        parent::sendEmailMessage($renderedTemplate, [$email => $name], $toEmail);
    }
}
