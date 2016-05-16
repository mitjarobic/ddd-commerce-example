<?php

namespace App\Modules\Commerce\DomainModel\Billing;


use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class DocumentWasCreatedEvent extends Event{

    use SerializesModels;

    /**
     * @var
     */
    public $filename;
    public $order;

    /**
     * Create a new event instance.
     *
     */
    public function __construct($order, $filename)
    {
        $this->order = $order;
        $this->filename = $filename;
    }

}
