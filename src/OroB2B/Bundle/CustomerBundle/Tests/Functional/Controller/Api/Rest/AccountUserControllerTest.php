<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountUserData;

/**
 * @dbIsolation
 */
class AccountUserControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [
                'OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountUserData',
                'OroB2B\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccountUserRoleData'
            ]
        );
    }

    /**
     * @return integer
     */
    public function testEnableAndDisable()
    {
        /** @var \OroB2B\Bundle\CustomerBundle\Entity\AccountUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadAccountUserData::EMAIL]);
        $id = $user->getId();

        $this->assertNotNull($user);
        $this->assertTrue($user->isEnabled());

        $this->client->request(
            'GET',
            $this->getUrl('orob2b_api_customer_disable_account_user', ['id' => $id])
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $this->getObjectManager()->clear();

        $user = $this->getUserRepository()->find($id);
        $this->assertFalse($user->isEnabled());

        $this->client->request(
            'GET',
            $this->getUrl('orob2b_api_customer_enable_account_user', ['id' => $id])
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $this->getObjectManager()->clear();

        $user = $this->getUserRepository()->find($id);
        $this->assertTrue($user->isEnabled());

        return $id;
    }

    /**
     * @depends testEnableAndDisable
     * @param int $id
     */
    public function testConfirm($id)
    {
        /** @var \OroB2B\Bundle\CustomerBundle\Entity\AccountUser $user */
        $user = $this->getUserRepository()->find($id);
        $this->assertNotNull($user);

        $user->setConfirmed(false);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $this->client->request(
            'GET',
            $this->getUrl('orob2b_api_customer_confirm_account_user', ['id' => $id])
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $user = $this->getUserRepository()->find($id);

        $this->assertNotNull($user);
        $this->assertTrue($user->isConfirmed());
    }

    /**
     * @depends testEnableAndDisable
     * @param integer $id
     */
    public function testDelete($id)
    {
        /** @var \OroB2B\Bundle\CustomerBundle\Entity\AccountUser $user */
        $user = $this->getUserRepository()->find($id);

        $this->assertNotNull($user);
        $id = $user->getId();

        $this->client->request('DELETE', $this->getUrl('orob2b_api_customer_delete_account_user', ['id' => $id]));
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->getObjectManager()->clear();
        $user = $this->getUserRepository()->find($id);

        $this->assertNull($user);
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
        return $this->getObjectManager()->getRepository('OroB2BCustomerBundle:AccountUser');
    }
}
