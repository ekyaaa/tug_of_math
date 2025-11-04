<?php

namespace App\Helpers;

use App\Models\GameModel;
use App\Models\PlayerModel;
use App\Models\QuestionModel;
use Illuminate\Support\Facades\Log;

class QuestionHelper
{
    /**
     * Generate a random math question with +, -, or × operators
     * 
     * @param GameModel $game
     * @param PlayerModel $player
     * @return QuestionModel|array
     */
    public static function generate(GameModel $game, PlayerModel $player, bool $returnArray = false)
    {
        // 1. Tentukan jumlah angka (2–4)
        $count = rand(2, 4);

        // 2. Generate angka dan operator
        $numbers = [];
        $operators = [];
        
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = rand(1, 9); // angka 1-9
        }
        
        // Generate operators: +, -, atau ×
        for ($i = 0; $i < $count - 1; $i++) {
            $rand = rand(0, 2); // 0, 1, atau 2
            if ($rand === 0) {
                $operators[] = '+';
            } elseif ($rand === 1) {
                $operators[] = '-';
            } else {
                $operators[] = '×';
            }
        }

        // 3. Bangun ekspresi string dan hitung hasil
        // Hitung dengan urutan prioritas: × dulu, baru + dan -
        $questionText = $numbers[0];
        $result = self::calculateExpression($numbers, $operators);

        // Build question text
        for ($i = 1; $i < $count; $i++) {
            $op = $operators[$i - 1];
            $num = $numbers[$i];
            $questionText .= " $op $num";
        }

        // Pastikan hasil tidak negatif dan tidak terlalu besar
        if ($result < 0 || $result > 100) {
            return self::generate($game, $player, $returnArray); // recursion
        }

        $questionText .= " = ?";
        $correctAnswer = $result;

        Log::info('Generated question', [
            'question' => $questionText,
            'answer' => $correctAnswer,
            'player_id' => $player->id
        ]);

        $question = QuestionModel::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'question_text' => $questionText,
            'correct_answer' => $correctAnswer
        ]);

        // Return array format for event or model for direct use
        if ($returnArray) {
            return [
                'id' => $question->id,
                'question_text' => $questionText,
                'player_id' => $player->id
            ];
        }

        return $question;
    }

    /**
     * Calculate expression with operator precedence (× before + and -)
     * 
     * @param array $numbers
     * @param array $operators
     * @return int
     */
    private static function calculateExpression(array $numbers, array $operators)
    {
        // Create a copy to manipulate
        $nums = $numbers;
        $ops = $operators;

        // First pass: handle multiplication
        $i = 0;
        while ($i < count($ops)) {
            if ($ops[$i] === '×') {
                // Multiply and collapse
                $nums[$i] = $nums[$i] * $nums[$i + 1];
                array_splice($nums, $i + 1, 1);
                array_splice($ops, $i, 1);
                // Don't increment i, check same position again
            } else {
                $i++;
            }
        }

        // Second pass: handle + and - from left to right
        $result = $nums[0];
        for ($i = 0; $i < count($ops); $i++) {
            if ($ops[$i] === '+') {
                $result += $nums[$i + 1];
            } else { // '-'
                $result -= $nums[$i + 1];
            }
        }

        return $result;
    }
}
