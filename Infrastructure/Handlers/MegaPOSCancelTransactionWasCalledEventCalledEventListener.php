<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use Datastat\MegaPOS\MegaPOSCancelTransactionWasCalledEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MegaPOSCancelTransactionWasCalledEventCalledEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MegaPOSCancelTransactionWasCalledEvent  $event
     * @return void
     */
    public function handle(MegaPOSCancelTransactionWasCalledEvent $event)
    {
        //
    }
}
