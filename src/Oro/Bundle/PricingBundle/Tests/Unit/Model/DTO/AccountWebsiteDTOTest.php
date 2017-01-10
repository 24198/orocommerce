<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\Model\DTO;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\PricingBundle\Model\DTO\AccountWebsiteDTO;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class AccountWebsiteDTOTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $website = new Website();
        $account = new Customer();
        $object = new AccountWebsiteDTO($account, $website);

        $this->assertSame($website, $object->getWebsite());
        $this->assertSame($account, $object->getAccount());
    }
}
