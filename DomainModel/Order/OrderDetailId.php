<?php

namespace App\Modules\Commerce\DomainModel\Order;

class OrderDetailId
{
    private $uuid;

    private function __construct($anUuid) {
        $this->uuid = $anUuid;
    }

    public static function create($anUuid) {
        return new static($anUuid);
    }

    public function uuid() {
        return $this->uuid;
    }

    public function equalsTo(OrderDetailId $anOrderDetailId) {
        return $anOrderDetailId->uuid() === $this->uuid();
    }
}