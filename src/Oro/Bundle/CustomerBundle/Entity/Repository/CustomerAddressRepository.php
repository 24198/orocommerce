<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\CustomerBundle\Entity\Account;

class CustomerAddressRepository extends AbstractDefaultTypedAddressRepository
{
    /**
     * @param Account $account
     * @param string $type
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getAddressesByType(Account $account, $type, AclHelper $aclHelper)
    {
        $query = $aclHelper->apply($this->getAddressesByTypeQueryBuilder($account, $type));

        return $query->getResult();
    }

    /**
     * @param Account $account
     * @param string $type
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getDefaultAddressesByType(Account $account, $type, AclHelper $aclHelper)
    {
        $query = $aclHelper->apply($this->getDefaultAddressesQueryBuilder($account, $type));

        return $query->getResult();
    }
}
