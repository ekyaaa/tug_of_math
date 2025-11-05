// Get data from window global variables (set by blade)
const playerId = window.__PLAYER_DATA__.playerId;
const playerSide = window.__PLAYER_DATA__.playerSide;
let gameId = window.__PLAYER_DATA__.gameId;
let currentQuestionId = window.__PLAYER_DATA__.currentQuestionId;
let currentAnswer = '';
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
        .listen('.game.over', function(data) {
            console.log('🏆 Game Over event received:', data);
            
            // Check if this player won or lost
            if (data.winnerId === playerId) {
                showWinnerModal(data.winnerName);
            } else if (data.loserId === playerId) {
                showLoserModal(data.winnerName);
            }
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

function showWinnerModal(winnerName) {
    // Disable number pad
    const numberButtons = document.querySelectorAll('.number-btn');
    numberButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    });
    
    // Create winner modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center bg-black/90 z-50';
    modal.innerHTML = `
        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-3xl p-12 text-center transform scale-0 transition-transform max-w-md">
            <div class="text-8xl mb-4">🏆</div>
            <h2 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">SELAMAT!</h2>
            <p class="text-3xl font-bold text-white mb-2">${winnerName}</p>
            <p class="text-2xl text-white/90 mb-8">Kamu adalah pemenangnya! 🎉</p>
            <div class="bg-white/20 rounded-2xl p-4 mb-6">
                <p class="text-white text-lg font-semibold">
                    Hebat! Kemampuan matematikamu luar biasa!
                </p>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Animate modal entrance
    setTimeout(() => {
        modal.firstElementChild.classList.add('scale-100');
        modal.firstElementChild.classList.remove('scale-0');
    }, 100);
}

function showLoserModal(winnerName) {
    // Disable number pad
    const numberButtons = document.querySelectorAll('.number-btn');
    numberButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    });
    
    // Array of motivational messages
    const motivationalMessages = [
        "Jangan menyerah! Coba lagi pasti bisa! 💪",
        "Kamu sudah bagus! Latihan lagi ya! 🌟",
        "Hampir menang! Sedikit lagi pasti bisa! ⚡",
        "Tetap semangat! Kamu pasti bisa lebih baik! 🚀",
        "Bagus sekali! Lain kali pasti menang! 🎯"
    ];
    
    const randomMessage = motivationalMessages[Math.floor(Math.random() * motivationalMessages.length)];
    
    // Create loser modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center bg-black/90 z-50';
    modal.innerHTML = `
        <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl p-12 text-center transform scale-0 transition-transform max-w-md">
            <div class="text-8xl mb-4">💪</div>
            <h2 class="text-4xl font-bold text-white mb-4 drop-shadow-lg">Game Berakhir</h2>
            <p class="text-2xl text-white/90 mb-2">Pemenang: ${winnerName}</p>
            <div class="bg-white/20 rounded-2xl p-6 my-6">
                <p class="text-white text-xl font-semibold mb-3">
                    ${randomMessage}
                </p>
                <p class="text-white/80 text-lg">
                    Setiap kesalahan adalah kesempatan untuk belajar! 📚
                </p>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Animate modal entrance
    setTimeout(() => {
        modal.firstElementChild.classList.add('scale-100');
        modal.firstElementChild.classList.remove('scale-0');
    }, 100);
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

// Make functions globally available
window.addNumber = addNumber;
window.clearAnswer = clearAnswer;
window.submitAnswer = submitAnswer;
window.showDebugInfo = showDebugInfo;
window.forceStart = forceStart;

// Start initialization when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeController);
} else {
    initializeController();
}
