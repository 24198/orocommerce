<?php

namespace OroB2B\Bundle\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;

abstract class AbstractDefaultTypedAddress extends AbstractTypedAddress
{
    /**
     * Many-to-one relation field, relation parameters must be in specific class
     *
     * @var object
     */
    protected $owner;

    /**
     * One-to-many relation field, relation parameters must be in specific class
     *
     * @var Collection
     **/
    protected $addressesToTypes;

    public function __construct()
    {
        $this->addressesToTypes = new ArrayCollection();
        parent::__construct();
    }

    /**
     * Get address types
     *
     * @return Collection|AddressType[]
     */
    public function getTypes()
    {
        $types = new ArrayCollection();
        /** @var AbstractAddressToAddressType $addressToType */
        foreach ($this->getAddressesToTypes() as $addressToType) {
            $types->add($addressToType->getType());
        }

        return $types;
    }

    /**
     * Set address types
     *
     * @param Collection $types
     * @return AbstractDefaultTypedAddress
     */
    public function setTypes(Collection $types)
    {
        $this->getAddressesToTypes()->clear();

        /** @var AddressType $type */
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    /**
     * Remove address type
     *
     * @param AddressType $type
     * @return AbstractDefaultTypedAddress
     */
    public function removeType(AddressType $type)
    {
        /** @var AbstractAddressToAddressType $addressesToType */
        foreach ($this->getAddressesToTypes() as $addressesToType) {
            if ($addressesToType->getType()->getName() === $type->getName()) {
                $this->removeAddressesToType($addressesToType);
            }
        }

        return $this;
    }

    /**
     * Add address type
     *
     * @param AddressType $type
     * @return AbstractDefaultTypedAddress
     */
    public function addType(AddressType $type)
    {
        $addressToType = $this->getAddressToAddressTypeEntity();
        $addressToType->setType($type);
        $addressToType->setAddress($this);
        $this->addAddressesToType($addressToType);

        return $this;
    }

    /**
     * Get default types
     *
     * @return Collection|AddressType[]
     */
    public function getDefaults()
    {
        $defaultTypes = new ArrayCollection();
        /** @var AbstractAddressToAddressType $addressToType */
        foreach ($this->getAddressesToTypes() as $addressToType) {
            if ($addressToType->isDefault()) {
                $defaultTypes->add($addressToType->getType());
            }
        }

        return $defaultTypes;
    }

    /**
     * Set default types
     *
     * @param Collection|AddressType[] $defaults
     * @return AbstractDefaultTypedAddress
     */
    public function setDefaults($defaults)
    {
        /** @var AbstractAddressToAddressType $addressToType */
        foreach ($this->getAddressesToTypes() as $addressToType) {
            $addressToType->setDefault(false);
            /** @var AddressType $default */
            foreach ($defaults as $default) {
                if ($addressToType->getType()->getName() === $default->getName()) {
                    $addressToType->setDefault(true);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Add addressesToTypes
     *
     * @param AbstractAddressToAddressType $addressesToTypes
     * @return AbstractDefaultTypedAddress
     */
    public function addAddressesToType(AbstractAddressToAddressType $addressesToTypes)
    {
        if (!$this->getAddressesToTypes()->contains($addressesToTypes)) {
            $this->addressesToTypes[] = $addressesToTypes;
        }

        return $this;
    }

    /**
     * Remove addressesToTypes
     *
     * @param AbstractAddressToAddressType $addressesToType
     * @return $this
     */
    public function removeAddressesToType(AbstractAddressToAddressType $addressesToType)
    {
        if ($this->hasAddressToType($addressesToType)) {
            $this->addressesToTypes->removeElement($addressesToType);
        }

        return $this;
    }

    /**
     * Get addressesToTypes
     *
     * @return Collection
     */
    public function getAddressesToTypes()
    {
        return $this->addressesToTypes;
    }

    /**
     * @param AbstractAddressToAddressType $addressToType
     * @return bool
     */
    protected function hasAddressToType(AbstractAddressToAddressType $addressToType)
    {
        return $this->getAddressesToTypes()->contains($addressToType);
    }

    /**
     * Set owner.
     *
     * @param $owner
     * @return $this
     */
    public function setOwner($owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return object
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Return entity for many-to-many relationship.
     * Should be compatible with AbstractAddressToAddressType
     *
     * @return AbstractAddressToAddressType
     */
    abstract protected function getAddressToAddressTypeEntity();
}
