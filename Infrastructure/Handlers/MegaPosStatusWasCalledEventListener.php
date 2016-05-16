<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use Datastat\MegaPOS\MegaPOSStatusWasCalledEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MegaPosStatusWasCalledEventListener
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
     * @param  MegaPOSStatusWasCalledEvent  $event
     * @return void
     */
    public function handle(MegaPOSStatusWasCalledEvent $event)
    {
        //
    }
}
