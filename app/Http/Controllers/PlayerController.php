<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\PlayerModel;
use App\Models\QuestionModel;
use App\Models\GameModel;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function submitAnswer(Request $request, GameModel $game, PlayerModel $player)
    {
        $question = QuestionModel::find($request->question_id);

        if ($question->answer == $request->answer) {
            // Update score player
            $player->increment('score', 1); // skor bertambah 1
        }

        // Trigger event ScoreUpdated
        // event(new ScoreUpdated(
        //     $game->players()->where('side', 'left')->first()->score,
        //     $game->players()->where('side', 'right')->first()->score
        // ));
    }
}
