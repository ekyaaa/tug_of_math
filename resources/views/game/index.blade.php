{{-- filepath: e:\Project\tug_of_math\resources\views\game\index.blade.php --}}
@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="min-h-screen flex items-center justify-center p-8">
    <div class="game-container rounded-3xl p-12 max-w-4xl w-full text-center shadow-2xl">
        <h1 class="text-6xl font-bold text-white mb-4 animate-pulse">
            🎮 TUG OF MATH 🎮
        </h1>
        <p class="text-2xl text-white mb-12 opacity-90">
            Battle of Brain & Speed!
        </p>

        <div class="space-y-6">
            <button onclick="startGame()" 
                class="bg-yellow-400 hover:bg-yellow-500 text-purple-900 font-bold text-2xl py-6 px-16 rounded-full shadow-lg transform hover:scale-110 transition-all duration-300">
                🚀 START GAME
            </button>

            <div class="text-white text-lg opacity-75">
                <p>👥 2 Players Required</p>
                <p>📱 Use Your Phone as Controller</p>
            </div>
        </div>
    </div>
</div>

<script>
function startGame() {
    // Show lobby (bisa pakai Alpine.js atau Vue untuk SPA behavior)
    window.location.href = '/game/lobby';
}
</script>
@endsection