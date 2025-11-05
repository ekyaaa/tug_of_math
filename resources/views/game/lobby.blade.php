{{-- filepath: e:\Project\tug_of_math\resources\views\game\lobby.blade.php --}}
@extends('layouts.app')

@section('title', 'Lobby')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center p-8">
    <div class="game-container rounded-3xl p-12 max-w-6xl w-full shadow-2xl">
        <h2 class="text-5xl font-bold text-white text-center mb-12">
            🎯 SCAN TO JOIN 🎯
        </h2>

        <div class="grid grid-cols-2 gap-12 mb-12">
            <!-- Left Player QR/Character -->
            <div class="text-center">
                <div id="left-qr-container" class="bg-white rounded-2xl p-8 mb-4 transform hover:scale-105 transition-all flex items-center justify-center">
                    <div id="qr-left"></div>
                </div>
                <div id="left-character-container" class="hidden bg-white rounded-2xl p-8 mb-4">
                    <div class="text-8xl mb-4">🧑</div>
                    <p id="left-player-name" class="text-2xl font-bold text-purple-900"></p>
                </div>
                <div class="bg-blue-500 rounded-xl p-4">
                    <h3 class="text-2xl font-bold text-white mb-2">👈 PLAYER LEFT</h3>
                    <p id="left-status" class="text-white text-lg">Waiting...</p>
                </div>
            </div>

            <!-- Right Player QR/Character -->
            <div class="text-center">
                <div id="right-qr-container" class="bg-white rounded-2xl p-8 mb-4 transform hover:scale-105 transition-all flex items-center justify-center">
                    <div id="qr-right"></div>
                </div>
                <div id="right-character-container" class="hidden bg-white rounded-2xl p-8 mb-4">
                    <div class="text-8xl mb-4">🧑</div>
                    <p id="right-player-name" class="text-2xl font-bold text-purple-900"></p>
                </div>
                <div class="bg-red-500 rounded-xl p-4">
                    <h3 class="text-2xl font-bold text-white mb-2">PLAYER RIGHT 👉</h3>
                    <p id="right-status" class="text-white text-lg">Waiting...</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-white text-xl mb-6 opacity-90">
                📱 Scan QR Code with your phone to join the game
            </p>
            <button id="start-btn" disabled 
                class="bg-gray-400 text-white font-bold text-2xl py-4 px-12 rounded-full cursor-not-allowed">
                ⏳ Waiting for Players...
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
@vite('resources/js/game/lobby.js')
@endsection