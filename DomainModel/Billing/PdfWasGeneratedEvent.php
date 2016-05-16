<?php

namespace App\Modules\Commerce\DomainModel\Billing;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PdfWasGeneratedEvent extends Event{

    use SerializesModels;

    /**
     * @var
     */
    public $filename;

    /**
     * Create a new event instance.
     *
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

}