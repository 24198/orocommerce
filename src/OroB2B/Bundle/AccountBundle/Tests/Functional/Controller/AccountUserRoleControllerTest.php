<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\AccountBundle\Entity\Account;
use OroB2B\Bundle\AccountBundle\Entity\AccountUser;
use OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccountUserData;

/**
 * @dbIsolation
 */
class AccountUserRoleControllerTest extends WebTestCase
{
    const TEST_ROLE = 'Test account user role';
    const UPDATED_TEST_ROLE = 'Updated test account user role';

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(
            [
                'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccounts',
                'OroB2B\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccountUserData'
            ]
        );
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orob2b_account_account_user_role_create'));

        $form = $crawler->selectButton('Save and Close')->form();
        $form['orob2b_account_account_user_role[label]'] = self::TEST_ROLE;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Account User Role has been saved', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orob2b_account_account_user_role_index'));
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains(self::TEST_ROLE, $result->getContent());
    }

    /**
     * @depend testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'account-account-user-roles-grid',
            [
                'account-account-user-roles-grid[_filter][label][value]' => self::TEST_ROLE
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orob2b_account_account_user_role_update', ['id' => $id])
        );

        /** @var \OroB2B\Bundle\AccountBundle\Entity\AccountUser $accountUser */
        $accountUser = $this->getUserRepository()->findOneBy(['email' => LoadAccountUserData::EMAIL]);
        $account = $this->getAccountRepository()->findOneBy(['name' => 'account.orphan']);
        $accountUser->setAccount($account);
        $this->getObjectManager()->flush();

        $this->assertNotNull($accountUser);
        $this->assertContains('Add note', $crawler->html());

        $form = $crawler->selectButton('Save and Close')->form();

        $token = $this->getContainer()->get('form.csrf_provider')
            ->generateCsrfToken('orob2b_account_account_user_role');
        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'input_action'        => '',
            'orob2b_account_account_user_role' => [
                '_token' => $token,
                'label' => self::UPDATED_TEST_ROLE,
                'account' => $account->getId(),
                'appendUsers' => $accountUser->getId(),
            ]
        ]);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $content = $crawler->html();
        $this->assertContains('Account User Role has been saved', $content);

        $this->getObjectManager()->clear();

        /** @var \OroB2B\Bundle\AccountBundle\Entity\AccountUserRole $role */
        $role = $this->getUserRoleRepository()->find($id);

        $this->assertNotNull($role);
        $this->assertEquals(self::UPDATED_TEST_ROLE, $role->getLabel());
        $this->assertNotEmpty($role->getRole());

        /** @var \OroB2B\Bundle\AccountBundle\Entity\AccountUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadAccountUserData::EMAIL]);

        $this->assertNotNull($user);
        $this->assertNotNull($user->getRole($role->getRole()));
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
        return $this->getObjectManager()->getRepository('OroB2BAccountBundle:AccountUser');
    }

    /**
     * @return \OroB2B\Bundle\AccountBundle\Entity\Repository\AccountRepository
     */
    protected function getAccountRepository()
    {
        return $this->getObjectManager()->getRepository('OroB2BAccountBundle:Account');
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getUserRoleRepository()
    {
        return $this->getObjectManager()->getRepository('OroB2BAccountBundle:AccountUserRole');
    }
}
