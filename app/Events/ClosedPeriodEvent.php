<?php

namespace App\Events;

use App\Models\ClosedPeriod;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClosedPeriodEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public ClosedPeriod $closedPeriod;

    /**
     * Create a new event instance.
     */
    public function __construct(ClosedPeriod $closedPeriod)
    {
        $this->closedPeriod = $closedPeriod;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
