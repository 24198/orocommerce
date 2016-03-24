<?php

namespace OroB2B\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use OroB2B\Bundle\ProductBundle\Model\ExtendProductImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="orob2b_product_image")
 * @Config
 */
class ProductImage extends ExtendProductImage
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var Product
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $product;

    /**
     * @var array
     * @ORM\Column(name="types", type="array", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $types;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return (array) $this->types;
    }

    /**
     * @param array|null $types
     */
    public function setTypes(array $types = null)
    {
        $this->types = $types;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return isset($this->types[$type]) && $this->types[$type];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __get($name)
    {
        return isset($this->types[$name]) && $this->types[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->types[$name] = (bool) $value;
    }
}
