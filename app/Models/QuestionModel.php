<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionModel extends Model
{
    use HasFactory;

    protected $table = 't_question';

    protected $fillable = [
        'game_id',
        'player_id',
        'question_text',
        'correct_answer',
        'player_answer'
    ];

    protected $casts = [
        'correct_answer' => 'integer',
        'player_answer' => 'integer'
    ];

    // Relationships
    public function game()
    {
        return $this->belongsTo(GameModel::class, 'game_id');
    }

    public function player()
    {
        return $this->belongsTo(PlayerModel::class, 'player_id');
    }
}
