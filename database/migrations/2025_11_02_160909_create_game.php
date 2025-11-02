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
        Schema::create('m_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player1_id')->constrained('m_player');
            $table->foreignId('player2_id')->constrained('m_player');
            $table->foreignId('winner_id')->nullable()->constrained('m_player');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game');
    }
};
