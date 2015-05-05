<?php

namespace OroB2B\Bundle\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Entity\Group as BaseGroup;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="orob2b_group")
 */
class Group extends BaseGroup
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $roles;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
