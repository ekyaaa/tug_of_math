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

        .chat-bubble {
            position: absolute;
            top: -60px;
            background: white;
            color: black;
            padding: 8px 12px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
            z-index: 20;
            white-space: nowrap;
            opacity: 0;
            transform: scale(0.8);
            transition: opacity 0.3s ease, transform 0.3s ease;
            width: fit-content;
            min-width: 60px;
        }

        .chat-bubble.show {
            opacity: 1;
            transform: scale(1);
        }

        .chat-bubble::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 20px;
            border-width: 8px;
            border-style: solid;
            border-color: white transparent transparent transparent;
        }

        @keyframes typing {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .typing-indicator {
            display: inline-block;
        }

        .typing-indicator span {
            display: inline-block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: black;
            margin: 0 1px;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>

    <!-- Background Music -->
    <audio id="background-music" loop>
        <source src="/music/bg_music.mp3" type="audio/mpeg">
        <source src="/music/bg_music.ogg" type="audio/ogg">
        Your browser does not support the audio element.
    </audio>

    <!-- Volume Control (Optional) -->
    <div class="fixed bottom-4 right-4 z-50 bg-white/10 backdrop-blur-sm rounded-full p-3 shadow-lg">
        <button id="music-toggle" onclick="toggleMusic()" class="text-white hover:text-yellow-300 transition-colors">
            <svg id="music-icon-playing" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
            </svg>
            <svg id="music-icon-muted" class="w-8 h-8 hidden" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    <div class="h-screen flex flex-col p-8">
        <!-- Tug of War Visual (Half Screen Height) -->
        <div class="game-container rounded-3xl mb-8 shadow-2xl overflow-hidden flex-shrink-0 flex flex-col justify-end"
            style="background-image: url('/images/bg.png'); 
                    background-size: 100% auto; 
                    background-position: bottom center; 
                    background-repeat: no-repeat;
                    height: calc(50vh - 2rem);
                    position: relative;">

            <div class="tug-container flex items-center justify-center relative" style="padding: 2rem;">
                <!-- Transparent Border Container -->
                <div class="border-overlay"></div>

                <div class="content-images flex items-stretch" style="gap: 0; height: 200px;">
                    <!-- Left Character -->
                    <div id="left-person" class="struggle-animation transition-all duration-500 relative"
                        style="width: auto; height: 100%;">
                        <div class="chat-bubble" id="left-bubble"></div>
                        <img src="/images/left.webp" alt="Left Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>

                    <!-- Rope -->
                    <div class="rope-animation" style="width: auto; height: 100%;">
                        <img src="/images/rope.webp" alt="Rope" style="width: auto; height: 100%; object-fit: contain;">
                    </div>
                    
                    <!-- Right Character -->
                    <div id="right-person" class="struggle-animation transition-all duration-500 relative"
                        style="width: auto; height: 100%;">
                        <div class="chat-bubble" id="right-bubble"></div>
                        <img src="/images/right.webp" alt="Right Player"
                            style="width: auto; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Display with Names (Fill Remaining Space) -->
        <div class="grid grid-cols-2 gap-8 flex-1">
            <!-- Left Question -->
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

            <!-- Right Question -->
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

    <script>
        const gameId = {{ $game->id }};
        const leftPlayer = @json($game->player1);
        const rightPlayer = @json($game->player2);
        let gameReady = false;
        let countdownInterval = null;
        let gameEnded = false; // Track if game has ended
        let backgroundMusic = null;
        let isMusicPlaying = false;

        // Track scores internally (not displayed)
        let leftScore = {{ $game->player1->score }};
        let rightScore = {{ $game->player2->score }};
        
        // Track consecutive correct answers for bubble chat
        let leftConsecutiveCorrect = 0;
        let rightConsecutiveCorrect = 0;
        
        // Chat phrases - varied lengths similar to "Ayo tarik kuat-kuat!"
        const leftPhrases = [
            "Ayo semangat!",
            "Jangan menyerah!",
            "Kita pasti bisa!",
            "Tarik lebih kuat!",
            "Ayo terus maju!",
            "Sedikit lagi menang!",
            "Keren, lanjutkan!",
            "Wah hebat sekali!",
            "Ayo terus begitu!",
            "Mantap jiwa nih!"
        ];
        
        const rightPhrases = [
            "Aku akan menang!",
            "Tidak akan kalah!",
            "Ini masih mudah!",
            "Ayo kita lawan!",
            "Siap-siap kalah!",
            "Rasakan kekuatanku!",
            "Belum apa-apa kok!",
            "Ini baru permulaan!",
            "Masih jauh menang!",
            "Jangan mimpi menang!"
        ];

        // Wait for Echo to be ready
        function initializeGameScreen() {
            if (typeof window.Echo === 'undefined') {
                console.log('Waiting for Echo...');
                setTimeout(initializeGameScreen, 100);
                return;
            }

            console.log('Echo is ready! Setting up game listeners...');
            setupGameListeners();
            
            // Initialize and play background music
            initializeMusic();

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

        function initializeMusic() {
            backgroundMusic = document.getElementById('background-music');
            
            // Set volume (0.0 to 1.0)
            backgroundMusic.volume = 0.5; // 30% volume, adjust as needed
            
            // Try to play music automatically
            backgroundMusic.play()
                .then(() => {
                    console.log('🎵 Background music started');
                    isMusicPlaying = true;
                    updateMusicIcon();
                })
                .catch(error => {
                    console.log('⚠️ Autoplay prevented, user interaction required:', error);
                    // Music will start when user interacts with the page
                    isMusicPlaying = false;
                    updateMusicIcon();
                });
            
            // Add event listener for music errors
            backgroundMusic.addEventListener('error', (e) => {
                console.error('❌ Error loading music:', e);
                console.log('💡 Make sure to place your music file at: public/music/background.mp3');
            });
        }

        function toggleMusic() {
            if (!backgroundMusic) {
                backgroundMusic = document.getElementById('background-music');
            }
            
            if (isMusicPlaying) {
                backgroundMusic.pause();
                isMusicPlaying = false;
                console.log('⏸️ Music paused');
                updateMusicIcon();
            } else {
                backgroundMusic.play()
                    .then(() => {
                        isMusicPlaying = true;
                        console.log('▶️ Music playing');
                        updateMusicIcon();
                    })
                    .catch(error => {
                        console.error('❌ Error playing music:', error);
                        updateMusicIcon();
                    });
            }
        }

        function updateMusicIcon() {
            const playingIcon = document.getElementById('music-icon-playing');
            const mutedIcon = document.getElementById('music-icon-muted');
            
            if (isMusicPlaying) {
                playingIcon.classList.remove('hidden');
                mutedIcon.classList.add('hidden');
            } else {
                playingIcon.classList.add('hidden');
                mutedIcon.classList.remove('hidden');
            }
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
                        
                        // Track consecutive correct answers for left player
                        leftConsecutiveCorrect++;
                        if (leftConsecutiveCorrect >= 2) {
                            showChatBubble('left', getRandomPhrase(leftPhrases));
                            leftConsecutiveCorrect = 0; // Reset after showing bubble
                        }
                    } else if (data.playerId === rightPlayer.id) {
                        rightScore = data.score;
                        
                        // Track consecutive correct answers for right player
                        rightConsecutiveCorrect++;
                        if (rightConsecutiveCorrect >= 2) {
                            showChatBubble('right', getRandomPhrase(rightPhrases));
                            rightConsecutiveCorrect = 0; // Reset after showing bubble
                        }
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
    
    function showChatBubble(side, message) {
        const bubbleId = side === 'left' ? 'left-bubble' : 'right-bubble';
        const bubble = document.getElementById(bubbleId);
        
        // Show typing indicator first
        bubble.innerHTML = '<span class="typing-indicator"><span></span><span></span><span></span></span>';
        bubble.classList.add('show');
        
        // After 800ms, show the actual message
        setTimeout(() => {
            bubble.textContent = message;
        }, 800);
        
        // Hide bubble after 3 seconds
        setTimeout(() => {
            bubble.classList.remove('show');
        }, 4000);
    }
    
    function getRandomPhrase(phrases) {
        return phrases[Math.floor(Math.random() * phrases.length)];
    }        function updateTugOfWar(leftScore, rightScore) {
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
            // Stop background music when game ends (optional - comment out if you want music to continue)
            if (backgroundMusic && isMusicPlaying) {
                backgroundMusic.pause();
                isMusicPlaying = false;
                updateMusicIcon();
                console.log('🎵 Music stopped - Game ended');
            }
            
            // Determine winner and loser
            const winnerId = name === leftPlayer.name ? leftPlayer.id : rightPlayer.id;
            const winnerName = name;
            const loserId = name === leftPlayer.name ? rightPlayer.id : leftPlayer.id;
            const loserName = name === leftPlayer.name ? rightPlayer.name : leftPlayer.name;
            
            // Broadcast game over event to controllers
            axios.post('/game/game-over', {
                winnerId: winnerId,
                winnerName: winnerName,
                loserId: loserId,
                loserName: loserName
            }).then(() => {
                console.log('✅ Game over event sent');
            }).catch(err => {
                console.error('❌ Error sending game over:', err);
            });
            
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
