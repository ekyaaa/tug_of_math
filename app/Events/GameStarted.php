<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $player1Id;
    public $player2Id;

    public function __construct($gameId, $player1Id, $player2Id)
    {
        $this->gameId = $gameId;
        $this->player1Id = $player1Id;
        $this->player2Id = $player2Id;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel');
    }

    public function broadcastAs()
    {
        return 'game.started';
    }
}
