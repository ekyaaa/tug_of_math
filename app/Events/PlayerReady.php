<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $playerId;
    public $side;

    public function __construct($playerId, $side)
    {
        $this->playerId = $playerId;
        $this->side = $side;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel');
    }

    public function broadcastAs()
    {
        return 'player.ready';
    }
}
