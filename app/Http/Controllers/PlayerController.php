<?php

namespace App\Http\Controllers;

use App\Events\PlayerScoreUpdated;
use App\Events\PlayerQuestionUpdated;
use App\Events\PlayerReady;
use App\Events\CountdownStart;
use App\Models\PlayerModel;
use App\Events\PlayerJoined;
use App\Models\QuestionModel;
use App\Models\GameModel;
use App\Helpers\QuestionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
        // Find game for this player
        $game = GameModel::where('player1_id', $player->id)
            ->orWhere('player2_id', $player->id)
            ->latest()
            ->first();
        
        // Get current question for this player
        $currentQuestion = null;
        if ($game) {
            $currentQuestion = QuestionModel::where('game_id', $game->id)
                ->where('player_id', $player->id)
                ->latest()
                ->first();
            
            // If game exists but no question, generate one now
            if (!$currentQuestion) {
                Log::info('🔧 No question found, generating new question', [
                    'game_id' => $game->id,
                    'player_id' => $player->id
                ]);
                $currentQuestion = QuestionHelper::generate($game, $player);
            }
        }

        return view('player.controller', compact('player', 'game', 'currentQuestion'));
    }

    public function playerReady(Request $request)
    {
        $playerId = $request->player_id;
        $side = $request->side;
        
        Log::info('Player ready', ['player_id' => $playerId, 'side' => $side]);
        
        // Mark player as ready in cache
        Cache::put("player_ready_{$playerId}", true, 600); // 10 minutes
        
        // Check if both players are ready
        $allPlayers = PlayerModel::orderBy('created_at', 'desc')->take(2)->get();
        
        if ($allPlayers->count() == 2) {
            $player1Ready = Cache::has("player_ready_{$allPlayers[0]->id}");
            $player2Ready = Cache::has("player_ready_{$allPlayers[1]->id}");
            
            Log::info('Ready check', [
                'player1' => $allPlayers[0]->id,
                'player1_ready' => $player1Ready,
                'player2' => $allPlayers[1]->id,
                'player2_ready' => $player2Ready
            ]);
            
            // If both ready, start countdown
            if ($player1Ready && $player2Ready) {
                Log::info('All players ready! Starting countdown...');
                event(new CountdownStart());
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function getCurrentQuestion(PlayerModel $player)
    {
        // Find game for this player
        $game = GameModel::where('player1_id', $player->id)
            ->orWhere('player2_id', $player->id)
            ->latest()
            ->first();
        
        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'No game found for player'
            ], 404);
        }
        
        // Get or create current question
        $question = QuestionModel::where('game_id', $game->id)
            ->where('player_id', $player->id)
            ->latest()
            ->first();
        
        if (!$question) {
            Log::info('🔧 Generating question on-demand', [
                'game_id' => $game->id,
                'player_id' => $player->id
            ]);
            $question = QuestionHelper::generate($game, $player);
        }
        
        return response()->json([
            'success' => true,
            'question' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'player_id' => $question->player_id
            ]
        ]);
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
            
            Log::info('✅ Correct answer!', [
                'player_id' => $player->id,
                'new_score' => $player->score,
                'question' => $question->question_text,
                'answer' => $request->player_answer
            ]);
            
            // Broadcast score update only when correct
            event(new PlayerScoreUpdated($player->id, $player->score));
        } else {
            Log::info('❌ Wrong answer', [
                'player_id' => $player->id,
                'question' => $question->question_text,
                'correct_answer' => $question->correct_answer,
                'player_answer' => $request->player_answer
            ]);
        }
        
        // Generate new question (whether correct or wrong)
        $game = $question->game;
        $newQuestion = QuestionHelper::generate($game, $player, true);
        
        // Broadcast new question
        event(new PlayerQuestionUpdated($player->id, $newQuestion));

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'new_score' => $player->score
        ]);
    }
}
