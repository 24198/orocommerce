<?php

namespace OroB2B\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroB2B\Bundle\CustomerBundle\Model\ExtendCustomer;

/**
 * @ORM\Entity(repositoryClass="OroB2B\Bundle\CustomerBundle\Entity\Repository\CustomerRepository")
 * @ORM\Table(
 *      name="orob2b_customer",
 *      indexes={
 *          @ORM\Index(name="orob2b_customer_name_idx", columns={"name"})
 *      }
 * )
 *
 * @Config(
 *      routeName="orob2b_customer_index",
 *      routeView="orob2b_customer_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-user"
 *          },
 *          "form"={
 *              "form_type"="orob2b_customer_select",
 *              "grid_name"="customer-customers-select-grid",
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          }
 *      }
 * )
 * @Config()
 */
class Customer extends ExtendCustomer
{
    const INTERNAL_RATING_CODE = 'cust_internal_rating';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\CustomerBundle\Entity\Customer", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var Collection|Customer[]
     *
     * @ORM\OneToMany(targetEntity="OroB2B\Bundle\CustomerBundle\Entity\Customer", mappedBy="parent")
     */
    protected $children;

    /**
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup", inversedBy="customers")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $group;

    /**
     * @ORM\OneToMany(
     *      targetEntity="OroB2B\Bundle\CustomerBundle\Entity\AccountUser",
     *      mappedBy="customer",
     *      cascade={"persist"}
     * )
     **/
    protected $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set parent
     *
     * @param Customer $parent
     * @return Customer
     */
    public function setParent(Customer $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Customer
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set group
     *
     * @param CustomerGroup $group
     * @return Customer
     */
    public function setGroup(CustomerGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return CustomerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add child
     *
     * @param Customer $child
     * @return Customer
     */
    public function addChild(Customer $child)
    {
        if (!$this->hasChild($child)) {
            $child->setParent($this);
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * Remove child
     *
     * @param Customer $child
     * @return Customer
     */
    public function removeChild(Customer $child)
    {
        if ($this->hasChild($child)) {
            $child->setParent(null);
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * Get children
     *
     * @return Collection|Customer[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Customer $child
     * @return bool
     */
    protected function hasChild(Customer $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @param AccountUser $user
     * @return Customer
     */
    public function addUser(AccountUser $accountUser)
    {
        if (!$this->hasUser($accountUser)) {
            $accountUser->setCustomer($this);
            $this->users->add($accountUser);
        }

        return $this;
    }

    /**
     * @param AccountUser $accountUser
     * @return Customer
     */
    public function removeUser(AccountUser $accountUser)
    {
        if ($this->hasUser($accountUser)) {
            $accountUser->setCustomer(null);
            $this->users->removeElement($accountUser);
        }

        return $this;
    }

    /**
     * Get children
     *
     * @return Collection|AccountUser[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param AccountUser $accountUser
     * @return bool
     */
    protected function hasUser(AccountUser $accountUser)
    {
        return $this->users->contains($accountUser);
    }
}
