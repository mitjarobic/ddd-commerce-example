<?php

namespace App\Modules\Commerce\DomainModel\Payment;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Embeddable */
class PaymentType
{
    const MONETA = 'moneta';
    const CARD = 'card';
    const PREBILL = 'prebill';

    /** @ORM\Column(type = "string", length=10) */
    private $payment;

    public static function moneta() {
        return new self(self::MONETA);
    }

    public static function card() {
        return new self(self::CARD);
    }

    public static function prebill() {
        return new self(self::PREBILL);
    }

    public static function fromKey($key) {
        if($key == self::PREBILL) return self::prebill();
        if($key == self::CARD) return self::card();
        if($key == self::MONETA) return self::moneta();
    }

    private function __construct($payment) {
        $this->payment = $payment;
    }

    public function equalsTo(self $payment) {
        return $this->payment === $payment->payment;
    }

    public function __toString(){
        return $this->payment;
    }
}