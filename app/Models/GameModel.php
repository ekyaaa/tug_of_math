<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameModel extends Model
{
    use HasFactory;

    protected $table = 'm_game';

    protected $fillable = [
        'player1_id',
        'player2_id',
        'winner_id'
    ];

    // Relationships
    public function player1()
    {
        return $this->belongsTo(PlayerModel::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(PlayerModel::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(PlayerModel::class, 'winner_id');
    }

    public function questions()
    {
        return $this->hasMany(QuestionModel::class, 'game_id');
    }

    // Method untuk mendapatkan semua players dalam game
    public function players()
    {
        return PlayerModel::whereIn('id', [$this->player1_id, $this->player2_id]);
    }
}
