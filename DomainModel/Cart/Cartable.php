<?php

namespace App\Modules\Commerce\DomainModel\Cart;


interface Cartable
{
    public function toCartArray();
}