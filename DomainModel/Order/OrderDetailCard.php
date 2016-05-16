<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Modules\Common\DomainModel\Money;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class OrderDetailCard extends OrderDetail
{
    /**
     * @ORM\Column(type="string")
     */
    private $type;

    public function __construct(OrderDetailId $orderDetailId, $productId, $name, Money $price, $quantity, $type){
        parent::__construct($orderDetailId, $productId, $name, $price, $quantity);
        $this->setType($type);
    }

    private function setType($type)
    {
        $this->type = $type;
    }
}