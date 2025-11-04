{{-- filepath: e:\Project\tug_of_math\resources\views\game\show.blade.php --}}
@extends('layouts.app')

@section('title', 'Game')

@section('content')
    <style>
        @keyframes struggle {

            0%,
            100% {
                transform: translateX(3px);
            }

            50% {
                transform: translateX(-3px);
            }
        }

        @keyframes swing {

            0%,
            100% {
                transform: translateX(3px);
            }

            50% {
                transform: translateX(-3px);
            }
        }


        .struggle-animation {
            animation: struggle 1s ease-in-out infinite;
        }

        .tug-container {
            position: relative;
            height: 300px;
        }


        .rope-animation {
            animation: swing 1s ease-in-out infinite;
        }

        .border-overlay {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 100%;
            border-left: 4px solid rgba(255, 255, 255, 0.5);
            border-right: 4px solid rgba(255, 255, 255, 0.5);
            pointer-events: none;
            z-index: 10;
        }
    </style>

    <div class="min-h-screen flex flex-col p-8">
        <!-- Tug of War Visual (Full Width at Top) -->
        <div class="game-container rounded-3xl p-8 mb-8 shadow-2xl">
            <div class="tug-container flex items-center justify-center relative">
                <!-- Transparent Border Container -->
                <div class="border-overlay"></div>

                <div class="content-images flex items-stretch" style="gap: 0; height: 200px;">
                    <!-- Left Character -->
                    <div id="left-person" class="struggle-animation transition-all duration-500"
                        style="width: auto; height: 100%;">
                        <img src="/images/left.webp" alt="Left Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>

                    <!-- Rope -->
                    <div class="rope-animation" style="width: auto; height: 100%;">
                        <img src="/images/rope.webp" alt="Rope" style="width: auto; height: 100%; object-fit: contain;">
                    </div>

                    <!-- Right Character -->
                    <div id="right-person" class="struggle-animation transition-all duration-500"
                        style="width: auto; height: 100%;">
                        <img src="/images/right.webp" alt="Right Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Display with Names -->
        <div class="grid grid-cols-2 gap-8">
            <!-- Left Question -->
            <div class="game-container rounded-3xl p-8 shadow-2xl">
                <div class="bg-blue-500/30 rounded-2xl p-6 text-center">
                    <h3 id="left-name" class="text-white text-3xl font-bold mb-6">{{ $game->player1->name }}</h3>
                    <h4 id="left-question" class="text-white text-5xl font-bold">
                        {{ $leftQuestion->question_text ?? 'Waiting...' }}
                    </h4>
                </div>
            </div>

            <!-- Right Question -->
            <div class="game-container rounded-3xl p-8 shadow-2xl">
                <div class="bg-red-500/30 rounded-2xl p-6 text-center">
                    <h3 id="right-name" class="text-white text-3xl font-bold mb-6">{{ $game->player2->name }}</h3>
                    <h4 id="right-question" class="text-white text-5xl font-bold">
                        {{ $rightQuestion->question_text ?? 'Waiting...' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <script>
        const gameId = {{ $game->id }};
        const leftPlayer = @json($game->player1);
        const rightPlayer = @json($game->player2);
        let gameReady = false;
        let countdownInterval = null;
        let gameEnded = false; // Track if game has ended

        // Track scores internally (not displayed)
        let leftScore = {{ $game->player1->score }};
        let rightScore = {{ $game->player2->score }};

        // Wait for Echo to be ready
        function initializeGameScreen() {
            if (typeof window.Echo === 'undefined') {
                console.log('Waiting for Echo...');
                setTimeout(initializeGameScreen, 100);
                return;
            }

            console.log('Echo is ready! Setting up game listeners...');
            setupGameListeners();

            // Emit device ready signal
            axios.post('/game/device-ready', {
                game_id: gameId,
                device_type: 'show'
            }).then(() => {
                console.log('✅ Show device ready signal sent');
            }).catch(err => {
                console.error('❌ Error sending device ready:', err);
            });
        }

        function showCountdown() {
            // Create countdown overlay
            const overlay = document.createElement('div');
            overlay.id = 'countdown-overlay';
            overlay.className = 'fixed inset-0 flex items-center justify-center bg-black/90 z-50';
            overlay.innerHTML = `
            <div class="text-center">
                <h2 class="text-white text-6xl font-bold mb-8">Get Ready!</h2>
                <div id="countdown-number" class="text-white text-9xl font-bold animate-pulse">3</div>
            </div>
        `;
            document.body.appendChild(overlay);

            let count = 3;
            countdownInterval = setInterval(() => {
                count--;
                if (count > 0) {
                    document.getElementById('countdown-number').textContent = count;
                } else {
                    document.getElementById('countdown-number').textContent = 'GO!';
                    setTimeout(() => {
                        overlay.remove();
                        gameReady = true;
                        console.log('🎮 Game is now READY!');
                    }, 1000);
                    clearInterval(countdownInterval);
                }
            }, 1000);
        }

        function setupGameListeners() {
            console.log('📡 Show screen subscribing to game-channel...');

            // Listen for countdown start from controller readiness
            window.Echo.channel('game-channel')
                .listen('.countdown.start', function(data) {
                    console.log('🎉 Countdown starting on show screen:', data);
                    showCountdown();
                })
                .listen('.player.score.updated', (data) => {
                    console.log('Score updated:', data);

                    // Update internal score tracking
                    if (data.playerId === leftPlayer.id) {
                        leftScore = data.score;
                    } else if (data.playerId === rightPlayer.id) {
                        rightScore = data.score;
                    }

                    // Animate tug of war (winner checking is now inside this function)
                    updateTugOfWar(leftScore, rightScore);
                })
                .listen('.player.question.updated', (data) => {
                    console.log('Question updated:', data);

                    if (data.playerId === leftPlayer.id) {
                        document.getElementById('left-question').textContent = data.question.question_text;
                    } else if (data.playerId === rightPlayer.id) {
                        document.getElementById('right-question').textContent = data.question.question_text;
                    }
                });

            console.log('✅ Game listeners setup complete!');

            // Debug: test connection
            window.Echo.connector.socket.on('connect', () => {
                console.log('✅ Socket.io CONNECTED in show! ID:', window.Echo.connector.socket.id);
            });
        }

        function updateTugOfWar(leftScore, rightScore) {
            if (gameEnded) return; // Don't update if game already ended
            
            const diff = leftScore - rightScore;
            const maxDiff = 10; // Maximum score difference before win
            const shift = diff * 30; // 30px per score difference (positive = left winning, negative = right winning)

            const contentImages = document.querySelector('.content-images');
            const borderOverlay = document.querySelector('.border-overlay');
            
            // Move entire group: left wins = negative shift (move left), right wins = positive shift (move right)
            contentImages.style.transform = `translateX(${-shift}px)`;

            // Check if rope center crossed the border (150px = half of 300px border width)
            if (Math.abs(shift) >= 150) {
                gameEnded = true; // Mark game as ended
                if (shift > 0) {
                    // Left player crossed the border (left won)
                    showWinner(leftPlayer.name);
                } else {
                    // Right player crossed the border (right won)
                    showWinner(rightPlayer.name);
                }
                return;
            }

            // Visual feedback for winner (using opacity)
            const leftPerson = document.getElementById('left-person');
            const rightPerson = document.getElementById('right-person');

            if (diff > 0) {
                leftPerson.style.opacity = '1';
                rightPerson.style.opacity = '0.7';
            } else if (diff < 0) {
                leftPerson.style.opacity = '0.7';
                rightPerson.style.opacity = '1';
            } else {
                leftPerson.style.opacity = '1';
                rightPerson.style.opacity = '1';
            }
        }

        function showWinner(name) {
            // Create winner modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 flex items-center justify-center bg-black/80 z-50';
            modal.innerHTML = `
            <div class="bg-white rounded-3xl p-12 text-center transform scale-0 transition-transform">
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

        // Start initialization when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeGameScreen);
        } else {
            initializeGameScreen();
        }
    </script>
@endsection
