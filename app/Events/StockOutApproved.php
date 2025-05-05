<?php

namespace App\Events;

use App\Models\StockOut;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockOutApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public StockOut $stockOut;

    /**
     * Create a new event instance.
     */
    public function __construct(StockOut $stockOut)
    {
        $this->stockOut = $stockOut;
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
