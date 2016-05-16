<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use App\Modules\Commerce\DomainModel\Order\OrderWasPlacedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyTheBoss
{
    /**
     * Create the event handler.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.	 *
     * @param  OrderWasStoredEvent  $event
     */
    public function handle(OrderWasPlacedEvent $event)
    {
        echo "NotifyTheBossEventHandler";
    }
}