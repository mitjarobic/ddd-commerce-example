<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Modules\Commerce\DomainModel\Cart\Cartable;

interface VariationToDetail
{
    public function transform(Cartable $variation);
}