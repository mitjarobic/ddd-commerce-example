<?php

namespace App\Modules\Commerce\DomainModel\Order;


class OrderId
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

    public function equalsTo(OrderId $anOrderId) {
        return $anOrderId->uuid() === $this->uuid();
    }
}