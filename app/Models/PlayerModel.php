<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerModel extends Model
{
    use HasFactory;

    protected $table = 'm_player';

    protected $fillable = [
        'name',
        'side',
        'score'
    ];

    protected $casts = [
        'score' => 'integer'
    ];

    // Relationships
    public function gamesAsPlayer1()
    {
        return $this->hasMany(GameModel::class, 'player1_id');
    }

    public function gamesAsPlayer2()
    {
        return $this->hasMany(GameModel::class, 'player2_id');
    }

    public function wonGames()
    {
        return $this->hasMany(GameModel::class, 'winner_id');
    }

    public function questions()
    {
        return $this->hasMany(QuestionModel::class, 'player_id');
    }
}
