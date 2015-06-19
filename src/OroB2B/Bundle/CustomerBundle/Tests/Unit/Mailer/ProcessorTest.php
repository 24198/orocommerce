<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Unit\Mailer;

use Oro\Bundle\UserBundle\Tests\Unit\Mailer\AbstractProcessorTest;

use OroB2B\Bundle\CustomerBundle\Mailer\Processor;
use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;

class ProcessorTest extends AbstractProcessorTest
{
    const PASSWORD = '123456';

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailProcessor;

    /**
     * @var AccountUser
     */
    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->user = new AccountUser();
        $this->user
            ->setEmail('email_to@example.com')
            ->setPlainPassword(self::PASSWORD)
            ->setConfirmationToken($this->user->generateToken());

        $this->mailProcessor = new Processor(
            $this->managerRegistry,
            $this->configManager,
            $this->renderer,
            $this->emailHolderHelper,
            $this->mailer
        );
    }

    protected function tearDown()
    {
        parent::tearDown();

        unset($this->user);
    }

    public function testSendWelcomeNotification()
    {
        $this->assertSendCalled(
            Processor::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'password' => self::PASSWORD],
            $this->buildMessage($this->user->getEmail())
        );

        $this->mailProcessor->sendWelcomeNotification($this->user, self::PASSWORD);
    }

    public function testSendConfirmationEmail()
    {
        $this->assertSendCalled(
            Processor::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'token' => $this->user->getConfirmationToken()],
            $this->buildMessage($this->user->getEmail())
        );

        $this->mailProcessor->sendConfirmationEmail($this->user, $this->user->getConfirmationToken());
    }
}
