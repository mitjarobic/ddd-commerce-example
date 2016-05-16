<?php

namespace App\Modules\Commerce\DomainModel\Order;


interface OrderRepository
{
    public function add(Order $anOrder);
    public function remove(Order $anOrder);
    public function nextIdentity();
}