<?php

namespace App\Modules\Commerce\DomainModel\Order;


use App\Modules\Common\DomainModel\Money;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class OrderDetailBenefit extends OrderDetail
{

    /**
     * @ORM\Column(type="boolean")
     */
    private $onCard=false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $discount=0;

    public function __construct(OrderDetailId $orderDetailId, $productId, $name, Money $price, $quantity, $description, $discount, $onCard){
        parent::__construct($orderDetailId, $productId, $name, $price, $quantity);
        $this->setDescription($description);
        $this->setDiscount($discount);
        $this->setOnCard($onCard);
    }

    private function setOnCard($onCard)
    {
        $this->onCard = $onCard;
    }

    private function setDescription($description)
    {
        $this->description = $description;
    }

    private function setDiscount($discount)
    {
        $this->discount = $discount;
    }
}