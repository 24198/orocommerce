<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional;

use Oro\Bundle\CustomerBundle\Entity\AccountUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class AccountUserActionsTest extends WebTestCase
{
    const EMAIL = LoadAccountUserData::EMAIL;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountUserRoleData'
            ]
        );
    }
    public function testConfirm()
    {
        /** @var \Oro\Bundle\CustomerBundle\Entity\AccountUser $user */
        $user = $this->getReference(static::EMAIL);
        $this->assertNotNull($user);

        $id = $user->getId();

        $user->setConfirmed(false);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $this->executeOperation($user, 'oro_account_accountuser_confirm');

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        $this->assertCount(1, $emailMessages);

        $message = array_shift($emailMessages);

        $this->assertInstanceOf('\Swift_Message', $message);
        $this->assertEquals($user->getEmail(), key($message->getTo()));
        $this->assertEquals(
            $this->getContainer()->get('oro_config.manager')->get('oro_notification.email_notification_sender_email'),
            key($message->getFrom())
        );
        $this->assertContains($user->getEmail(), $message->getSubject());
        $this->assertContains($user->getEmail(), $message->getBody());

        $configManager = $this->getContainer()->get('oro_config.manager');
        $loginUrl = trim($configManager->get('oro_ui.application_url'), '/')
            . $this->getUrl('oro_customer_account_user_security_login');

        $this->assertContains($loginUrl, $message->getBody());

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        $user = $this->getUserRepository()->find($id);

        $this->assertNotNull($user);
        $this->assertTrue($user->isConfirmed());
    }

    public function testSendConfirmation()
    {
        /** @var AccountUser $user */
        $email = static::EMAIL;

        $user = $this->getReference($email);
        $user->setConfirmed(false);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $this->executeOperation($user, 'oro_account_accountuser_sendconfirmation');

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        /** @var \Swift_Plugins_MessageLogger $emailLogging */
        $emailLogger = $this->getContainer()->get('swiftmailer.plugin.messagelogger');
        $emailMessages = $emailLogger->getMessages();

        /** @var \Swift_Message $message */
        $message = reset($emailMessages);

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($email, key($message->getTo()));
        $this->assertContains('Confirmation of account registration', $message->getSubject());
        $this->assertContains($email, $message->getBody());
    }

    public function testEnableAndDisable()
    {
        /** @var AccountUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => static::EMAIL]);
        $id = $user->getId();

        $this->assertNotNull($user);
        $this->assertTrue($user->isEnabled());

        $this->executeOperation($user, 'oro_account_accountuser_disable');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->getObjectManager()->clear();

        $user = $this->getUserRepository()->find($id);
        $this->assertFalse($user->isEnabled());

        $this->executeOperation($user, 'oro_account_accountuser_enable');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->getObjectManager()->clear();

        $user = $this->getUserRepository()->find($id);
        $this->assertTrue($user->isEnabled());
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getUserRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:AccountUser');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOperation(AccountUser $accountUser, $operationName)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'route' => 'oro_customer_account_user_view',
                    'entityId' => $accountUser->getId(),
                    'entityClass' => 'Oro\Bundle\CustomerBundle\Entity\AccountUser'
                ]
            )
        );
    }
}
