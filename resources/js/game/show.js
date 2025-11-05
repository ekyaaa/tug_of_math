document.addEventListener('DOMContentLoaded', () => {
    if (!window.__GAME__) {
        console.error('❌ window.__GAME__ not found');
        return;
    }
    const {
        id: gameId,
        leftPlayer,
        rightPlayer,
        leftScore: initialLeftScore,
        rightScore: initialRightScore
    } = window.__GAME__;

    let leftScore = initialLeftScore;
    let rightScore = initialRightScore;


    let gameReady = false;
    let countdownInterval = null;
    let gameEnded = false; // Track if game has ended
    let backgroundMusic = null;
    let isMusicPlaying = false;

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
            .listen('.countdown.start', function (data) {
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
            gameEnded = true;
            const winner = diff > 0 ? leftPlayer.name : rightPlayer.name;
            setTimeout(() => showWinner(winner), 500);
            return;
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
});