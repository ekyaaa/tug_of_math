{{-- filepath: e:\Project\tug_of_math\resources\views\game\show.blade.php --}}
@extends('layouts.app')

@section('title', 'Game')

@section('content')
<div class="min-h-screen flex flex-col p-8">
    <!-- Score Display -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <!-- Left Player Score -->
        <div class="game-container rounded-3xl p-8 text-center shadow-2xl">
            <h3 id="left-name" class="text-white text-3xl font-bold mb-4">{{ $game->player1->name }}</h3>
            <div class="bg-blue-500 rounded-2xl py-6">
                <p id="left-score" class="text-white text-7xl font-bold">{{ $game->player1->score }}</p>
            </div>
        </div>

        <!-- Center (Tug of War Visual) -->
        <div class="game-container rounded-3xl p-8 flex items-center justify-center shadow-2xl">
            <div class="flex items-center space-x-4">
                <div id="left-person" class="text-6xl transform transition-all duration-500">
                    🧑
                </div>
                <div class="text-6xl rope-animation">
                    🪢
                </div>
                <div id="right-person" class="text-6xl transform transition-all duration-500">
                    🧑
                </div>
            </div>
        </div>

        <!-- Right Player Score -->
        <div class="game-container rounded-3xl p-8 text-center shadow-2xl">
            <h3 id="right-name" class="text-white text-3xl font-bold mb-4">{{ $game->player2->name }}</h3>
            <div class="bg-red-500 rounded-2xl py-6">
                <p id="right-score" class="text-white text-7xl font-bold">{{ $game->player2->score }}</p>
            </div>
        </div>
    </div>

    <!-- Questions Display -->
    <div class="grid grid-cols-2 gap-8">
        <!-- Left Question -->
        <div class="game-container rounded-3xl p-8 shadow-2xl">
            <div class="bg-blue-500/30 rounded-2xl p-6 text-center">
                <p class="text-white text-2xl mb-4">👈 Left Question</p>
                <h4 id="left-question" class="text-white text-5xl font-bold">
                    Waiting...
                </h4>
            </div>
        </div>

        <!-- Right Question -->
        <div class="game-container rounded-3xl p-8 shadow-2xl">
            <div class="bg-red-500/30 rounded-2xl p-6 text-center">
                <p class="text-white text-2xl mb-4">Right Question 👉</p>
                <h4 id="right-question" class="text-white text-5xl font-bold">
                    Waiting...
                </h4>
            </div>
        </div>
    </div>
</div>

<script>
    const gameId = {{ $game->id }};
    const leftPlayer = @json($game->player1);
    const rightPlayer = @json($game->player2);

    // Listen for score updates menggunakan Echo
    window.Echo.channel('game-channel')
        .listen('.player.score.updated', (data) => {
            console.log('Score updated:', data);
            
            // Update score berdasarkan player_id
            if (data.playerId === leftPlayer.id) {
                document.getElementById('left-score').textContent = data.score;
            } else if (data.playerId === rightPlayer.id) {
                document.getElementById('right-score').textContent = data.score;
            }

            // Animate tug of war
            const leftScore = parseInt(document.getElementById('left-score').textContent);
            const rightScore = parseInt(document.getElementById('right-score').textContent);
            updateTugOfWar(leftScore, rightScore);

            // Check winner
            if (leftScore >= 10) {
                showWinner(leftPlayer.name);
            } else if (rightScore >= 10) {
                showWinner(rightPlayer.name);
            }
        })
        .listen('.player.question.updated', (data) => {
            console.log('Question updated:', data);
            
            if (data.playerId === leftPlayer.id) {
                document.getElementById('left-question').textContent = data.question.question_text;
            } else if (data.playerId === rightPlayer.id) {
                document.getElementById('right-question').textContent = data.question.question_text;
            }
        });

    function updateTugOfWar(leftScore, rightScore) {
        const diff = leftScore - rightScore;
        const maxDiff = 5; // Maximum visual shift
        const shift = Math.max(-maxDiff, Math.min(maxDiff, diff)) * 20; // 20px per score difference

        document.getElementById('left-person').style.transform = `translateX(${shift}px) ${diff > 0 ? 'scale(1.2)' : 'scale(1)'}`;
        document.getElementById('right-person').style.transform = `translateX(${shift}px) ${diff < 0 ? 'scale(1.2)' : 'scale(1)'}`;
    }

    function showWinner(name) {
        // Create winner modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 flex items-center justify-center bg-black/80 z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-3xl p-12 text-center transform scale-0">
                <h2 class="text-6xl font-bold text-purple-900 mb-4">🎉 WINNER 🎉</h2>
                <p class="text-4xl font-bold text-yellow-500 mb-8">${name}</p>
                <button onclick="location.href='/'" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-2xl py-4 px-12 rounded-full">
                    Play Again
                </button>
            </div>
        `;
        document.body.appendChild(modal);
        setTimeout(() => {
            modal.firstElementChild.classList.add('scale-100');
            modal.firstElementChild.classList.remove('scale-0');
        }, 100);
    }
</script>
@endsection