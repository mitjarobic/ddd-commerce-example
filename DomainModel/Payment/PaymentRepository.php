<?php

namespace App\Modules\Commerce\DomainModel\Payment;


interface PaymentRepository
{
    public function add(Payment $anPayment);
    public function remove(Payment $anPayment);
    public function nextIdentity();
}