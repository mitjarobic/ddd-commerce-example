<?php

namespace App\Modules\Commerce\DomainModel\Payment;


class PaymentId
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

    public function equalsTo(PaymentId $anPaymentId) {
        return $anPaymentId->uuid() === $this->uuid();
    }
}