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
        const playerId = {{ $player->id }};
        const playerSide = '{{ $player->side }}';
        let gameId = {{ $game->id ?? 'null' }};
        let currentAnswer = '';
        let currentQuestionId = {{ $currentQuestion->id ?? 'null' }};
        let gameReady = false;

        console.log('🎮 Controller initialized with:', {
            playerId,
            playerSide,
            gameId,
            currentQuestionId
        });

        // Wait for Echo to be ready
        function initializeController() {
            if (typeof window.Echo === 'undefined') {
                console.log('Waiting for Echo...');
                setTimeout(initializeController, 100);
                return;
            }

            console.log('Echo is ready! Setting up controller listeners...');
            setupControllerListeners();

            // Jika sudah ada gameId (dari server), emit ready langsung
            if (gameId) {
                const deviceType = playerSide === 'left' ? 'controller-left' : 'controller-right';
                axios.post('/game/device-ready', {
                    game_id: gameId,
                    device_type: deviceType
                }).then(() => {
                    console.log(`✅ ${deviceType} device ready signal sent (existing game)`);
                }).catch(err => {
                    console.error('❌ Error sending device ready:', err);
                });
            } else {
                console.log('⏳ Waiting for game.started event to get gameId...');
            }
        }

        function setupControllerListeners() {
            console.log('📡 Controller subscribing to game-channel...');

            // Listen for countdown start
            window.Echo.channel('game-channel')
                .listen('.countdown.start', function(data) {
                    console.log('🎉 Countdown starting on controller:', data);
                    startCountdown();
                })
                .listen('.player.score.updated', function(data) {
                    console.log('Score updated:', data);
                    if (data.playerId === playerId) {
                        document.getElementById('player-score').textContent = data.score;
                    }
                })
                .listen('.player.question.updated', function(data) {
                    console.log('📝 Question updated for player:', data);
                    if (data.playerId === playerId) {
                        currentQuestionId = data.question.id;
                        console.log('✅ Current question ID set to:', currentQuestionId);
                        clearAnswer();
                    } else {
                        console.log('⏭️ Question update for different player:', data.playerId, 'vs', playerId);
                    }
                })
                .listen('.game.started', function(data) {
                    console.log('🎮 Game started event received:', data);
                    console.log('📦 Full data object:', JSON.stringify(data, null, 2));

                    // Set gameId from event
                    gameId = data.gameId;
                    console.log('📌 GameId set to:', gameId);

                    // Debug: Check what we have
                    console.log('🔍 Checking questions:', {
                        'has question1': !!data.question1,
                        'has question2': !!data.question2,
                        'question1': data.question1,
                        'question2': data.question2,
                        'playerId': playerId
                    });

                    // Set current question based on player ID
                    // Only update if we don't already have a question ID from server
                    if (!currentQuestionId || currentQuestionId === 'null') {
                        if (data.question1 && data.question1.player_id === playerId) {
                            currentQuestionId = data.question1.id;
                            console.log('📝 Initial question set from event (player1):', currentQuestionId, data.question1.question_text);
                        } else if (data.question2 && data.question2.player_id === playerId) {
                            currentQuestionId = data.question2.id;
                            console.log('📝 Initial question set from event (player2):', currentQuestionId, data.question2.question_text);
                        } else {
                            console.error('❌ No matching question found for player:', playerId);
                        }
                    } else {
                        console.log('✅ Already have question ID from server:', currentQuestionId);
                    }

                    console.log('🎯 Current state:', {
                        gameId,
                        currentQuestionId,
                        playerId
                    });

                    // Emit device ready now that we have gameId
                    const deviceType = playerSide === 'left' ? 'controller-left' : 'controller-right';
                    axios.post('/game/device-ready', {
                        game_id: gameId,
                        device_type: deviceType
                    }).then(() => {
                        console.log(`✅ ${deviceType} device ready signal sent (gameId: ${gameId})`);
                        
                        // Double check: if still no question, request from server
                        if (!currentQuestionId || currentQuestionId === 'null') {
                            console.log('⚠️ Still no question after game started, fetching from server...');
                            fetchCurrentQuestion();
                        }
                    }).catch(err => {
                        console.error('❌ Error sending device ready:', err);
                    });
                });

            console.log('✅ Controller listeners setup complete!');

            // Debug: test connection
            window.Echo.connector.socket.on('connect', () => {
                console.log('✅ Socket.io CONNECTED in controller! ID:', window.Echo.connector.socket.id);
            });
        }

        function fetchCurrentQuestion() {
            if (!gameId) {
                console.log('⏳ No gameId yet, will retry after game.started event');
                return;
            }
            
            axios.get(`/player/${playerId}/current-question`)
                .then(response => {
                    if (response.data.question) {
                        currentQuestionId = response.data.question.id;
                        console.log('✅ Fetched current question from server:', currentQuestionId);
                    } else {
                        console.log('⚠️ No question available yet from server');
                    }
                })
                .catch(err => {
                    console.error('❌ Error fetching question:', err);
                });
        }

        function startCountdown() {
            console.log('⏰ Starting countdown...');
            const overlay = document.getElementById('countdown-overlay');
            const countdownText = overlay.querySelector('h2');
            const loadingDots = overlay.querySelector('.flex.justify-center');
            const countdownNumber = document.getElementById('countdown-number');

            // Hide loading, show countdown
            countdownText.textContent = 'Get Ready!';
            loadingDots.classList.add('hidden');
            countdownNumber.classList.remove('hidden');

            let count = 3;
            countdownNumber.textContent = count;

            const interval = setInterval(() => {
                count--;
                console.log('⏱️ Countdown:', count);
                if (count > 0) {
                    countdownNumber.textContent = count;
                } else {
                    countdownNumber.textContent = 'GO!';
                    console.log('🚀 GO! Hiding overlay and setting gameReady = true');
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                        gameReady = true;
                        console.log('✅ Game is now READY! gameReady =', gameReady);
                    }, 1000);
                    clearInterval(interval);
                }
            }, 1000);
        }

        function addNumber(num) {
            if (currentAnswer === '0') currentAnswer = '';
            currentAnswer += num;
            updateDisplay();
        }

        function clearAnswer() {
            currentAnswer = '';
            updateDisplay();
        }

        function updateDisplay() {
            document.getElementById('answer-display').value = currentAnswer || '0';
        }

        function showDebugInfo() {
            const info = `
🐛 DEBUG INFO:
━━━━━━━━━━━━━━━━━━━
Player ID: ${playerId}
Player Side: ${playerSide}
Game ID: ${gameId}
Current Question ID: ${currentQuestionId}
Current Answer: ${currentAnswer}
Game Ready: ${gameReady}
Echo Available: ${typeof window.Echo !== 'undefined'}
Socket Connected: ${window.Echo?.connector?.socket?.connected || 'N/A'}
            `.trim();
            
            console.log(info);
            alert(info);
        }

        function forceStart() {
            console.log('⚡ Force starting game...');
            document.getElementById('countdown-overlay').classList.add('hidden');
            gameReady = true;
            console.log('✅ Game forced to READY! gameReady =', gameReady);
            alert('Game forced to start! You can now submit answers.');
        }

        async function submitAnswer() {
            console.log('🎯 Submit answer clicked!', {
                currentAnswer,
                currentQuestionId,
                gameReady
            });

            if (!currentAnswer) {
                console.warn('⚠️ No answer entered!');
                alert('Masukkan jawaban dulu!');
                return;
            }

            if (!currentQuestionId) {
                console.warn('⚠️ No question ID!');
                alert('Belum ada soal!');
                return;
            }

            if (!gameReady) {
                console.warn('⚠️ Game not ready yet!');
                alert('Game belum dimulai!');
                return;
            }

            try {
                console.log('📤 Sending answer...', {
                    url: `/player/${playerId}/answer`,
                    question_id: currentQuestionId,
                    player_answer: parseInt(currentAnswer)
                });

                const response = await axios.post(`/player/${playerId}/answer`, {
                    question_id: currentQuestionId,
                    player_answer: parseInt(currentAnswer)
                });

                console.log('✅ Answer submitted successfully!', response.data);

                // Visual feedback based on correctness
                const display = document.getElementById('answer-display');
                if (response.data.is_correct) {
                    display.style.backgroundColor = 'rgba(34, 197, 94, 0.3)'; // green
                    console.log('🎉 Correct answer!');
                } else {
                    display.style.backgroundColor = 'rgba(239, 68, 68, 0.3)'; // red
                    console.log('❌ Wrong answer');
                }

                // Reset visual feedback after delay
                setTimeout(() => {
                    display.style.backgroundColor = 'transparent';
                }, 1000);

            } catch (error) {
                console.error('❌ Error submitting answer:', error);
                console.error('Error details:', error.response?.data);
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        }

        // Start initialization when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeController);
        } else {
            initializeController();
        }
    </script>
@endsection
