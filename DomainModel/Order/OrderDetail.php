<?php

namespace App\Modules\Commerce\DomainModel\Order;


use App\Modules\Common\DomainModel\IdentifiableDomainObject;
use App\Modules\Common\DomainModel\Money;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * This class represents a OrderDetail item, either a CardVariationDetail or an BenefitVariationDetail.
 * @ORM\Entity
 * @ORM\Table(name="order_details")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap( {"card" = "App\Modules\Commerce\DomainModel\Order\OrderDetailCard", "benefit" = "App\Modules\Commerce\DomainModel\Order\OrderDetailBenefit"} )
 */
abstract class OrderDetail extends IdentifiableDomainObject
{

    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $product_id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="money")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity=1;

    public function __construct(OrderDetailId $orderDetailId,  $productId, $name, Money $price, $quantity)
    {
        $this->setId($orderDetailId);
        $this->setProductId($productId);
        $this->setName($name);
        $this->setPrice($price);
        $this->setQuantity($quantity);
    }

    private function setProductId($productId)
    {
        $exploded = explode('_', $productId);
        $this->product_id = count($exploded) == 2 ? $exploded[1] : $exploded[0];
//        $this->product_type = count($exploded) == 2 ? $exploded[0] : 'benefit';
    }

    private function setName($name)
    {
        $this->name = $name;
    }

    private function setPrice(Money $price)
    {
        $this->price = $price;
    }

    private function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    private function setData($data)
    {
        $this->data = $data;
    }

}