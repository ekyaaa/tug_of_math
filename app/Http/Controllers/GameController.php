<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameModel;
use App\Models\PlayerModel;
use App\Models\QuestionModel;
use App\Events\GameStarted;
use App\Events\CountdownStart;
use App\Events\DeviceReady;
use App\Helpers\QuestionHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GameController extends Controller
{
    public function index()
    {
        return view('game.index');
    }

    public function lobby()
    {
        return view('game.lobby');
    }

    public function show(GameModel $game)
    {
        // Load game dengan relationships dan questions
        $game->load(['player1', 'player2', 'questions']);
        
        // Get current questions for each player
        $leftQuestion = $game->questions()->where('player_id', $game->player1_id)->latest()->first();
        $rightQuestion = $game->questions()->where('player_id', $game->player2_id)->latest()->first();
        
        return view('game.show', compact('game', 'leftQuestion', 'rightQuestion'));
    }

    public function createGame(Request $request)
    {
        // Find existing players by name and side, or create new ones
        $player1 = PlayerModel::where('side', 'left')
            ->where('name', $request->player1_name)
            ->latest()
            ->first();
        
        if (!$player1) {
            $player1 = PlayerModel::create([
                'name' => $request->player1_name,
                'side' => 'left',
                'score' => 0
            ]);
            Log::info('Created new player1', ['id' => $player1->id, 'name' => $player1->name]);
        } else {
            Log::info('Using existing player1', ['id' => $player1->id, 'name' => $player1->name]);
        }

        $player2 = PlayerModel::where('side', 'right')
            ->where('name', $request->player2_name)
            ->latest()
            ->first();
        
        if (!$player2) {
            $player2 = PlayerModel::create([
                'name' => $request->player2_name,
                'side' => 'right',
                'score' => 0
            ]);
            Log::info('Created new player2', ['id' => $player2->id, 'name' => $player2->name]);
        } else {
            Log::info('Using existing player2', ['id' => $player2->id, 'name' => $player2->name]);
        }

        $game = GameModel::create([
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'winner_id' => null
        ]);

        Log::info('🎮 Game created', [
            'game_id' => $game->id,
            'player1_id' => $player1->id,
            'player1_name' => $player1->name,
            'player2_id' => $player2->id,
            'player2_name' => $player2->name
        ]);

        // Generate initial questions for both players
        $question1 = QuestionHelper::generate($game, $player1);
        $question2 = QuestionHelper::generate($game, $player2);

        Log::info('🎮 Questions generated', [
            'q1' => $question1->question_text,
            'q2' => $question2->question_text
        ]);

        // Trigger game started event with questions
        $eventData = [
            'gameId' => $game->id,
            'player1Id' => $player1->id,
            'player2Id' => $player2->id,
            'question1' => [
                'id' => $question1->id,
                'question_text' => $question1->question_text,
                'player_id' => $player1->id
            ],
            'question2' => [
                'id' => $question2->id,
                'question_text' => $question2->question_text,
                'player_id' => $player2->id
            ]
        ];
        
        Log::info('🔥 Broadcasting GameStarted event', $eventData);
        
        event(new GameStarted(
            $game->id, 
            $player1->id, 
            $player2->id,
            [
                'id' => $question1->id,
                'question_text' => $question1->question_text,
                'player_id' => $player1->id
            ],
            [
                'id' => $question2->id,
                'question_text' => $question2->question_text,
                'player_id' => $player2->id
            ]
        ));

        return response()->json($game->load('player1', 'player2'));
    }

    public function startCountdown(Request $request)
    {
        $gameId = $request->game_id;
        Log::info('🔥 Manual countdown trigger', ['game_id' => $gameId]);
        
        // Broadcast countdown start event
        event(new CountdownStart());
        
        return response()->json(['success' => true]);
    }

    public function deviceReady(Request $request)
    {
        $gameId = $request->game_id;
        $deviceType = $request->device_type; // 'show', 'controller-left', 'controller-right'
        
        Log::info('Device ready', ['game_id' => $gameId, 'device_type' => $deviceType]);
        
        // Mark device as ready in cache
        $cacheKey = "game_{$gameId}_device_{$deviceType}_ready";
        Cache::put($cacheKey, true, 600); // 10 minutes
        
        // Check if all 3 devices are ready
        $showReady = Cache::has("game_{$gameId}_device_show_ready");
        $leftReady = Cache::has("game_{$gameId}_device_controller-left_ready");
        $rightReady = Cache::has("game_{$gameId}_device_controller-right_ready");
        
        Log::info('Device ready check', [
            'game_id' => $gameId,
            'show' => $showReady,
            'left' => $leftReady,
            'right' => $rightReady
        ]);
        
        // If all 3 devices ready, trigger countdown
        if ($showReady && $leftReady && $rightReady) {
            Log::info('🎉 All devices ready! Starting countdown...');
            event(new CountdownStart());
            
            // Clear cache
            Cache::forget("game_{$gameId}_device_show_ready");
            Cache::forget("game_{$gameId}_device_controller-left_ready");
            Cache::forget("game_{$gameId}_device_controller-right_ready");
        }
        
        return response()->json(['success' => true]);
    }

    public function generateQrPlayer(string $side)
    {
        $ip = gethostbyname(gethostname()); // IP lokal
        $port = 8000;
        $url = "http://{$ip}:{$port}/join/{$side}";

        $qr = QrCode::size(200)->generate($url);

        return view('qr', compact('qr')); 
    }
}
