<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $name;
    public $side;

    public function __construct($name, $side)
    {
        $this->name = $name;
        $this->side = $side;
        
        Log::info('PlayerJoined event constructed', [
            'name' => $name,
            'side' => $side,
            'channel' => 'lobby',
            'event' => 'player.joined'
        ]);
    }

    public function broadcastOn()
    {
        $channel = new Channel('lobby');
        Log::info('Broadcasting on channel', ['channel' => 'lobby']);
        return $channel;
    }

    public function broadcastAs()
    {
        return 'player.joined';
    }
    
    public function broadcastWith()
    {
        $data = [
            'name' => $this->name,
            'side' => $this->side,
            'timestamp' => now()->toIso8601String()
        ];
        
        Log::info('Broadcasting data', $data);
        
        return $data;
    }
}