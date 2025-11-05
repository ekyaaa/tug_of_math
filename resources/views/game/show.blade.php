{{-- filepath: resources/views/game/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Game')

@push('styles')
    @vite('resources/css/game/show.css')
@endpush

@section('content')
    <!-- Background Music -->
    <audio id="background-music" loop>
        <source src="/music/bg_music.mp3" type="audio/mpeg">
        <source src="/music/bg_music.ogg" type="audio/ogg">
        Your browser does not support the audio element.
    </audio>

    <!-- Volume Control -->
    <div class="fixed bottom-4 right-4 z-50 bg-white/10 backdrop-blur-sm rounded-full p-3 shadow-lg">
        <button id="music-toggle" onclick="toggleMusic()" class="text-white hover:text-yellow-300 transition-colors">
            <svg id="music-icon-playing" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
            </svg>
            <svg id="music-icon-muted" class="w-8 h-8 hidden" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="h-screen flex flex-col p-8">
        <!-- Tug of War Visual -->
        <div class="game-container rounded-3xl mb-8 shadow-2xl overflow-hidden flex-shrink-0 flex flex-col justify-end"
            style="background-image: url('/images/bg.png'); 
                    background-size: 100% auto; 
                    background-position: bottom center; 
                    background-repeat: no-repeat;
                    height: calc(50vh - 2rem);
                    position: relative;">

            <div class="tug-container flex items-center justify-center relative" style="padding: 2rem;">
                <div class="border-overlay"></div>

                <div class="content-images flex items-stretch" style="gap: 0; height: 200px;">
                    <div id="left-person" class="struggle-animation transition-all duration-500 relative"
                        style="width: auto; height: 100%;">
                        <div class="chat-bubble" id="left-bubble"></div>
                        <img src="/images/left.webp" alt="Left Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>

                    <div class="rope-animation" style="width: auto; height: 100%;">
                        <img src="/images/rope.webp" alt="Rope" style="width: auto; height: 100%; object-fit: contain;">
                    </div>

                    <div id="right-person" class="struggle-animation transition-all duration-500 relative"
                        style="width: auto; height: 100%;">
                        <div class="chat-bubble" id="right-bubble"></div>
                        <img src="/images/right.webp" alt="Right Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Display -->
        <div class="grid grid-cols-2 gap-8 flex-1">
            <div class="rounded-3xl shadow-2xl overflow-hidden"
                style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
                <div class="h-full flex items-center justify-center p-8">
                    <div class="text-center w-full">
                        <h3 id="left-name" class="text-white text-4xl font-bold mb-8 drop-shadow-lg">
                            {{ $game->player1->name }}</h3>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 border-2 border-white/30">
                            <h4 id="left-question" class="text-white text-6xl font-bold drop-shadow-lg">
                                {{ $leftQuestion->question_text ?? 'Waiting...' }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl shadow-2xl overflow-hidden"
                style="background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);">
                <div class="h-full flex items-center justify-center p-8">
                    <div class="text-center w-full">
                        <h3 id="right-name" class="text-white text-4xl font-bold mb-8 drop-shadow-lg">
                            {{ $game->player2->name }}</h3>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 border-2 border-white/30">
                            <h4 id="right-question" class="text-white text-6xl font-bold drop-shadow-lg">
                                {{ $rightQuestion->question_text ?? 'Waiting...' }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Passing PHP data to JS --}}
    <script>
        window.__GAME__ = {
            id: {{ $game->id }},
            leftPlayer: @json($game->player1),
            rightPlayer: @json($game->player2),
            leftScore: {{ $game->player1->score }},
            rightScore: {{ $game->player2->score }}
        };
    </script>

    @vite('resources/js/game/show.js')
@endsection
