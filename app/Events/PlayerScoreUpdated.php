<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $playerId;
    public $score;

    public function __construct($playerId, $score)
    {
        $this->playerId = $playerId;
        $this->score = $score;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel'); // channel publik
    }

    public function broadcastAs()
    {
        return 'player.score.updated';
    }
}
