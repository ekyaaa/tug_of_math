<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameModel;
use App\Models\PlayerModel;
use App\Models\QuestionModel;
use App\Events\GameStarted;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        return view('game.show', compact('game'));
    }

    public function createGame(Request $request)
    {
        $player1 = PlayerModel::create([
            'name' => $request->player1_name,
            'side' => 'left',
            'score' => 0
        ]);

        $player2 = PlayerModel::create([
            'name' => $request->player2_name,
            'side' => 'right',
            'score' => 0
        ]);

        $game = GameModel::create([
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'winner_id' => null
        ]);

        // Generate initial questions for both players
        $this->generateQuestion($game, $player1);
        $this->generateQuestion($game, $player2);

        // Trigger game started event
        event(new GameStarted($game->id, $player1->id, $player2->id));

        return response()->json($game->load('player1', 'player2'));
    }

    public function generateQrPlayer(string $side)
    {
        $ip = gethostbyname(gethostname()); // IP lokal
        $port = 8000;
        $url = "http://{$ip}:{$port}/join/{$side}";

        $qr = QrCode::size(200)->generate($url);

        return view('qr', compact('qr')); 
    }

    private function generateQuestion(GameModel $game, PlayerModel $player)
    {
        // Array operasi matematika
        $operations = ['+', '-', '*', '/'];
        $operation = $operations[array_rand($operations)];

        // Generate angka berdasarkan operasi untuk hasil yang lebih masuk akal
        switch ($operation) {
            case '+':
                // Penjumlahan: angka 1-50
                $num1 = rand(1, 50);
                $num2 = rand(1, 50);
                $correctAnswer = $num1 + $num2;
                break;

            case '-':
                // Pengurangan: pastikan hasilnya positif
                $num1 = rand(10, 50);
                $num2 = rand(1, $num1); // num2 lebih kecil dari num1
                $correctAnswer = $num1 - $num2;
                break;

            case '*':
                // Perkalian: angka lebih kecil agar tidak terlalu sulit
                $num1 = rand(2, 15);
                $num2 = rand(2, 12);
                $correctAnswer = $num1 * $num2;
                break;

            case '/':
                // Pembagian: pastikan hasilnya bilangan bulat
                $num2 = rand(2, 12); // pembagi
                $correctAnswer = rand(1, 20); // hasil yang diinginkan
                $num1 = $num2 * $correctAnswer; // angka yang dibagi
                break;

            default:
                $num1 = rand(1, 10);
                $num2 = rand(1, 10);
                $correctAnswer = $num1 + $num2;
        }

        $questionText = "$num1 $operation $num2";

        $question = QuestionModel::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'question_text' => $questionText,
            'correct_answer' => $correctAnswer
        ]);

        return $question;
    }
}
