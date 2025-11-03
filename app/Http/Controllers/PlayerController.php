<?php

namespace App\Http\Controllers;

use App\Events\PlayerScoreUpdated;
use App\Events\PlayerQuestionUpdated;
use App\Models\PlayerModel;
use App\Events\PlayerJoined;
use App\Models\QuestionModel;
use App\Models\GameModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    public function showJoinPage(string $side)
    {
        return view('player.join', compact('side'));
    }

    public function submitJoin(Request $request, string $side)
    {
        Log::info('Player joining', ['name' => $request->name, 'side' => $side]);
        
        // CREATE PLAYER
        $player = PlayerModel::create([
            'name' => $request->name,
            'side' => $side,
            'score' => 0
        ]);

        Log::info('Player created', ['player_id' => $player->id, 'name' => $player->name]);

        // TRIGGER EVENT - INI YANG PENTING!
        event(new PlayerJoined($player->name, $side));
        
        Log::info('PlayerJoined event fired', ['name' => $player->name, 'side' => $side]);

        // RETURN RESPONSE
        return response()->json([
            'success' => true,
            'message' => 'Berhasil join',
            'side' => $side,
            'player_id' => $player->id,
            'player_name' => $player->name
        ]);
    }

    public function showController(PlayerModel $player)
    {
        return view('player.controller', compact('player'));
    }

    public function submitAnswer(Request $request, PlayerModel $player)
    {
        $question = QuestionModel::find($request->question_id);
        
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found'], 404);
        }
        
        $question->update(['player_answer' => $request->player_answer]);

        $isCorrect = false;
        if ($question->correct_answer == $request->player_answer) {
            $player->increment('score', 1);
            $isCorrect = true;
            
            Log::info('Correct answer!', [
                'player_id' => $player->id,
                'new_score' => $player->score
            ]);
        }

        // Broadcast score update
        event(new PlayerScoreUpdated($player->id, $player->score));
        
        // Generate new question
        $game = $question->game;
        $newQuestion = $this->generateQuestion($game, $player);
        
        // Broadcast new question
        event(new PlayerQuestionUpdated($player->id, $newQuestion));

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'new_score' => $player->score
        ]);
    }

    private function generateQuestion(GameModel $game, PlayerModel $player)
    {
        $operations = ['+', '-', '*', '/'];
        $operation = $operations[array_rand($operations)];

        switch ($operation) {
            case '+':
                $num1 = rand(1, 50);
                $num2 = rand(1, 50);
                $answer = $num1 + $num2;
                break;

            case '-':
                $num1 = rand(10, 50);
                $num2 = rand(1, $num1);
                $answer = $num1 - $num2;
                break;

            case '*':
                $num1 = rand(2, 15);
                $num2 = rand(2, 12);
                $answer = $num1 * $num2;
                break;

            case '/':
                $num2 = rand(2, 12);
                $answer = rand(2, 15);
                $num1 = $num2 * $answer;
                break;
        }

        $questionText = "$num1 $operation $num2";

        $question = QuestionModel::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'question_text' => $questionText,
            'correct_answer' => $answer,
        ]);

        return [
            'id' => $question->id,
            'question_text' => $questionText,
            'player_id' => $player->id
        ];
    }
}
