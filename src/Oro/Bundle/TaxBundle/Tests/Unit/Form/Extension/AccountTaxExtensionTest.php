<?php

namespace Oro\Bundle\TaxBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Form\Type\AccountType;
use Oro\Bundle\TaxBundle\Entity\AccountTaxCode;
use Oro\Bundle\TaxBundle\Form\Extension\AccountTaxExtension;

class AccountTaxExtensionTest extends AbstractAccountTaxExtensionTest
{
    /**
     * @return AccountTaxExtension
     */
    protected function getExtension()
    {
        return new AccountTaxExtension($this->doctrineHelper, 'OroTaxBundle:AccountTaxCode');
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(AccountType::NAME, $this->getExtension()->getExtendedType());
    }

    public function testOnPostSubmitNewAccount()
    {
        $this->prepareDoctrineHelper(true, true);

        $account = $this->createTaxCodeTarget();
        $event = $this->createEvent($account);

        $taxCode = $this->createTaxCode(1);

        $this->assertTaxCodeAdd($event, $taxCode);
        $this->entityRepository->expects($this->once())
            ->method($this->getRepositoryFindMethod());

        $this->getExtension()->onPostSubmit($event);

        $this->assertEquals([$account], $taxCode->getAccounts()->toArray());
    }

    public function testOnPostSubmitExistingAccount()
    {
        $this->prepareDoctrineHelper(true, true);

        $account = $this->createTaxCodeTarget();
        $event = $this->createEvent($account);

        $newTaxCode = $this->createTaxCode(1);
        $taxCodeWithAccount = $this->createTaxCode(2);
        $taxCodeWithAccount->addAccount($account);

        $this->assertTaxCodeAdd($event, $newTaxCode);
        $this->entityRepository->expects($this->once())
            ->method($this->getRepositoryFindMethod())
            ->will($this->returnValue($taxCodeWithAccount));

        $this->getExtension()->onPostSubmit($event);

        $this->assertEquals([$account], $newTaxCode->getAccounts()->toArray());
        $this->assertEquals([], $taxCodeWithAccount->getAccounts()->toArray());
    }

    /**
     * @param int|null $id
     *
     * @return Customer
     */
    protected function createTaxCodeTarget($id = null)
    {
        return $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', ['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryFindMethod()
    {
        return 'findOneByAccount';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTestableCollection(AccountTaxCode $accountTaxCode)
    {
        return $accountTaxCode->getAccounts();
    }
}
