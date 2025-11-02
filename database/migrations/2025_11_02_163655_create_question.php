<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('m_game');
            $table->foreignId('player_id')->constrained('m_player');
            $table->string('question_text');
            $table->integer('correct_answer');
            $table->integer('player_answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question');
    }
};
