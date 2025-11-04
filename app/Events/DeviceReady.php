<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $deviceType; // 'show', 'controller-left', 'controller-right'

    public function __construct($gameId, $deviceType)
    {
        $this->gameId = $gameId;
        $this->deviceType = $deviceType;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel');
    }

    public function broadcastAs()
    {
        return 'device.ready';
    }
}
