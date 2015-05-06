<?php

namespace OroB2B\Bundle\CustomerAdminBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroB2B\Bundle\CustomerBundle\Entity\AbstractCustomerGroup;

/**
 * @Config()
 */
class CustomerGroup extends AbstractCustomerGroup
{
    /**
     * @var Collection|Customer[]
     *
     * @ORM\OneToMany(targetEntity="OroB2B\Bundle\CustomerAdminBundle\Entity\Customer", mappedBy="group")
     **/
    protected $customers;
}
