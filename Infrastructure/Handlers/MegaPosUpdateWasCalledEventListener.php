<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use Datastat\MegaPOS\MegaPOSUpdateWasCalledEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MegaPosUpdateWasCalledEventListener
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
     * @param  MegaPOSUpdateWasCalledEvent  $event
     * @return void
     */
    public function handle(MegaPOSUpdateWasCalledEvent $event)
    {
        //
    }
}
