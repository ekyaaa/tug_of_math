{{-- filepath: e:\Project\tug_of_math\resources\views\player\controller.blade.php --}}
@extends('layouts.app')

@section('title', 'Controller')

@section('content')
    <div class="min-h-screen flex flex-col p-4">
        <!-- Countdown Overlay -->
        <div id="countdown-overlay" class="fixed inset-0 flex items-center justify-center bg-black/90 z-50">
            <div class="text-center">
                <h2 class="text-white text-4xl font-bold mb-4">Waiting for all players...</h2>
                <div class="flex justify-center space-x-2 mb-8">
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce"></div>
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <div id="countdown-number" class="hidden text-white text-9xl font-bold animate-pulse"></div>
            </div>
        </div>

        <!-- Player Info & Score -->
        <div class="game-container rounded-3xl p-8 mb-6 shadow-2xl">
            <div class="text-center">
                <h2 class="text-white text-3xl font-bold mb-4">{{ $player->name }}</h2>
                <div class="bg-white/20 rounded-2xl p-4 mt-4">
                    <p class="text-white text-2xl">Your Answer:</p>
                    <input type="text" id="answer-display" readonly
                        class="w-full bg-transparent text-white text-6xl font-bold text-center border-none focus:outline-none"
                        value="0">
                </div>
                <!-- Debug button -->
                <button onclick="showDebugInfo()" class="mt-4 bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded-lg text-sm">
                    🐛 Debug Info
                </button>
                <button onclick="forceStart()" class="mt-4 ml-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">
                    ⚡ Force Start Game
                </button>
            </div>
        </div>

        <!-- Number Pad -->
        <div class="game-container rounded-3xl p-6 shadow-2xl">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <button onclick="addNumber(1)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">1</button>
                <button onclick="addNumber(2)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">2</button>
                <button onclick="addNumber(3)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">3</button>
                <button onclick="addNumber(4)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">4</button>
                <button onclick="addNumber(5)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">5</button>
                <button onclick="addNumber(6)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">6</button>
                <button onclick="addNumber(7)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">7</button>
                <button onclick="addNumber(8)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">8</button>
                <button onclick="addNumber(9)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">9</button>
                <button onclick="clearAnswer()"
                    class="number-btn bg-red-500 hover:bg-red-600 text-white font-bold text-3xl py-6 rounded-2xl shadow-lg">C</button>
                <button onclick="addNumber(0)"
                    class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">0</button>
                <button onclick="submitAnswer()"
                    class="number-btn bg-green-500 hover:bg-green-600 text-white font-bold text-3xl py-6 rounded-2xl shadow-lg">✓</button>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JS
        window.__PLAYER_DATA__ = {
            playerId: {{ $player->id }},
            playerSide: '{{ $player->side }}',
            gameId: {{ $game->id ?? 'null' }},
            currentQuestionId: {{ $currentQuestion->id ?? 'null' }}
        };
    </script>
    @vite('resources/js/player/controller.js')
@endsection
