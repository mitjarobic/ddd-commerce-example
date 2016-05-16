<?php

namespace App\Modules\Commerce\DomainModel\Payment\Statement;

use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class DocumentWasImportedEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var
     */
    public $document;

    /**
     * Create a new event instance.
     *
     */
    public function __construct($document)
    {
        $this->document = $document;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['admin-channel'];
    }
}