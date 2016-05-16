<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class OrderWasPlacedEvent extends Event
{
    use SerializesModels;

    /**
     * @var
     */
    public $orderId;

    /**
     * Create a new event instance.
     *
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }
}