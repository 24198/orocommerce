<?php

namespace OroB2B\Bundle\AttributeBundle\Migrations\Data\ORM;

use OroB2B\Bundle\AttributeBundle\Entity\Attribute;
use OroB2B\Bundle\AttributeBundle\Entity\AttributeLabel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AbstractLoadAttributeData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->attributes as $item) {
            // Create attribute label
            $label = new AttributeLabel();
            $label->setValue($item['label']);

            // Create attribute
            $attribute = new Attribute();
            $attribute->setCode($item['code']);
            $attribute->setType($item['type']);
            $attribute->setSharingType('global');
            $attribute->setLocalized($item['localized']);
            $attribute->setSystem($item['system']);
            $attribute->setRequired($item['required']);
            $attribute->setUnique($item['unique']);
            $attribute->addLabel($label);

            $manager->persist($attribute);
        }

        $manager->flush();
        $manager->clear();
    }
}
