<?php

namespace App\Modules\Commerce\DomainModel\Payment\Statement;

use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class StatementWasImportedEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var
     */
    public $statement;

    /**
     * Create a new event instance.
     *
     */
    public function __construct($statement)
    {
        $this->statement = $statement;
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