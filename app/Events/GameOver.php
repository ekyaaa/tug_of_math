<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameOver implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $winnerId;
    public $winnerName;
    public $loserId;
    public $loserName;

    public function __construct($winnerId, $winnerName, $loserId, $loserName)
    {
        $this->winnerId = $winnerId;
        $this->winnerName = $winnerName;
        $this->loserId = $loserId;
        $this->loserName = $loserName;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel');
    }

    public function broadcastAs()
    {
        return 'game.over';
    }

    public function broadcastWith()
    {
        return [
            'winnerId' => $this->winnerId,
            'winnerName' => $this->winnerName,
            'loserId' => $this->loserId,
            'loserName' => $this->loserName
        ];
    }
}
