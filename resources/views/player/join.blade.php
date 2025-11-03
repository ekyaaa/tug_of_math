{{-- filepath: e:\Project\tug_of_math\resources\views\player\join.blade.php --}}
@extends('layouts.app')

@section('title', 'Join Game')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="game-container rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-bold text-white mb-2">
                @if($side === 'left')
                    👈 LEFT SIDE
                @else
                    RIGHT SIDE 👉
                @endif
            </h2>
            <p class="text-white text-lg opacity-90">Enter your name to join</p>
        </div>

        <form id="join-form" class="space-y-6">
            <div>
                <input 
                    type="text" 
                    id="player-name" 
                    placeholder="Your Name" 
                    required
                    class="w-full px-6 py-4 text-2xl text-center rounded-full border-4 border-white bg-white/90 focus:outline-none focus:ring-4 focus:ring-yellow-400"
                >
            </div>

            <button 
                type="submit"
                class="w-full bg-yellow-400 hover:bg-yellow-500 text-purple-900 font-bold text-2xl py-4 rounded-full shadow-lg transform hover:scale-105 transition-all">
                JOIN GAME 🎮
            </button>
        </form>

        <div id="waiting-screen" class="hidden text-center">
            <div class="animate-pulse">
                <p class="text-white text-3xl mb-4">✅ Joined!</p>
                <p class="text-white text-xl mb-6">Waiting for game to start...</p>
                <div class="flex justify-center space-x-2">
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce"></div>
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-4 h-4 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const side = '{{ $side }}';
    let playerId = null;

    // Wait for Echo to be ready
    function initializeJoinPage() {
        if (typeof window.Echo === 'undefined') {
            console.log('Waiting for Echo...');
            setTimeout(initializeJoinPage, 100);
            return;
        }

        console.log('Echo is ready!');
        setupJoinForm();
    }

    function setupJoinForm() {
        document.getElementById('join-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('player-name').value;
            
            try {
                const response = await axios.post(`/join/${side}`, { name });
                playerId = response.data.player_id;
                
            console.log('Join response:', response.data);
                
                // Show waiting screen
                document.getElementById('join-form').classList.add('hidden');
                document.getElementById('waiting-screen').classList.remove('hidden');
                
                // Listen for game start
                window.Echo.channel('game-channel')
                    .listen('.game.started', function(data) {
                        console.log('Game started:', data);
                        // Redirect to player controller page
                        window.location.href = `/player/${playerId}/controller`;
                    });
                
            } catch (error) {
                console.error('Error joining game:', error);
                alert('Error joining game: ' + (error.response?.data?.message || error.message));
            }
        });
    }

    // Start initialization when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeJoinPage);
    } else {
        initializeJoinPage();
    }
</script>
@endsection