<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerQuestionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $playerId;
    public $question;

    public function __construct($playerId, $question)
    {
        $this->playerId = $playerId;
        $this->question = $question;
    }

    public function broadcastOn()
    {
        return new Channel('game-channel'); // channel publik
    }

    public function broadcastAs()
    {
        return 'player.question.updated';
    }

    public function broadcastWith()
    {
        return [
            'playerId' => $this->playerId,
            'question' => $this->question,
        ];
    }
}
