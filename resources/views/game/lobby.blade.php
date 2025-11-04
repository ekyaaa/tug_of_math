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
<script>
    function initializeLobby() {
        if (typeof window.Echo === 'undefined') {
            console.log('Waiting for Echo...');
            setTimeout(initializeLobby, 100);
            return;
        }

        console.log('✅ Echo is ready!');
        console.log('Socket.io connected:', window.Echo.connector.socket.connected);

        const ip = window.location.hostname;
        const port = window.location.port;
        
        // Generate QR codes
        new QRCode(document.getElementById("qr-left"), {
            text: `http://${ip}:${port}/join/left`,
            width: 256,
            height: 256
        });
        
        new QRCode(document.getElementById("qr-right"), {
            text: `http://${ip}:${port}/join/right`,
            width: 256,
            height: 256
        });

        let leftJoined = false;
        let rightJoined = false;
        let leftPlayerName = '';
        let rightPlayerName = '';
        let gameId = null;

        console.log('📡 Subscribing to lobby channel...');

        // Subscribe to lobby channel
        const channel = window.Echo.channel('lobby');
        
        console.log('Channel subscribed:', channel);

        function handlePlayerJoined(data) {
            console.log('🎉 Processing player join:', data);
            
            if (data.side === 'left') {
                // Hide QR, show character
                document.getElementById('left-qr-container').classList.add('hidden');
                document.getElementById('left-character-container').classList.remove('hidden');
                document.getElementById('left-player-name').textContent = data.name;
                document.getElementById('left-status').textContent = `✅ ${data.name} Ready!`;
                
                leftJoined = true;
                leftPlayerName = data.name;
                console.log('✅ Left player joined:', data.name);
            } else if (data.side === 'right') {
                // Hide QR, show character
                document.getElementById('right-qr-container').classList.add('hidden');
                document.getElementById('right-character-container').classList.remove('hidden');
                document.getElementById('right-player-name').textContent = data.name;
                document.getElementById('right-status').textContent = `✅ ${data.name} Ready!`;
                
                rightJoined = true;
                rightPlayerName = data.name;
                console.log('✅ Right player joined:', data.name);
            }

            console.log('Current status:', { leftJoined, rightJoined, leftPlayerName, rightPlayerName });

            // Enable start button if both players joined
            if (leftJoined && rightJoined) {
                console.log('✅ Both players ready! Enabling start button...');
                const startBtn = document.getElementById('start-btn');
                startBtn.disabled = false;
                startBtn.className = 'bg-green-500 hover:bg-green-600 text-white font-bold text-2xl py-4 px-12 rounded-full transform hover:scale-110 transition-all';
                startBtn.textContent = '🎮 START GAME!';
                startBtn.onclick = createGame;
            }
        }

        // Listen menggunakan Echo channel listener (tanpa dot prefix)
        channel.listen('player.joined', (data) => {
            console.log('🎉 Method 1 - Echo listener:', data);
            handlePlayerJoined(data);
        });

        // Listen dengan dot prefix
        channel.listen('.player.joined', (data) => {
            console.log('🎉 Method 2 - Echo with dot:', data);
            handlePlayerJoined(data);
        });

        // Listen event name saja
        channel.listen('PlayerJoined', (data) => {
            console.log('🎉 Method 3 - Class name:', data);
            handlePlayerJoined(data);
        });

        // Backup: Listen langsung ke socket.io event
        window.Echo.connector.socket.on('lobby:player.joined', (data) => {
            console.log('🎉 Method 4 - Socket.io RAW:', data);
            handlePlayerJoined(data);
        });

        // Listen dengan App\\Events format
        window.Echo.connector.socket.on('lobby:App\\Events\\PlayerJoined', (data) => {
            console.log('🎉 Method 5 - Full namespace:', data);
            handlePlayerJoined(data);
        });

        // Monitor connection status
        window.Echo.connector.socket.on('connect', () => {
            console.log('✅ Socket.io CONNECTED in lobby! ID:', window.Echo.connector.socket.id);
        });

        window.Echo.connector.socket.on('disconnect', () => {
            console.log('❌ Socket.io disconnected in lobby!');
        });

        async function createGame() {
            try {
                console.log('🎮 Creating game with players:', leftPlayerName, rightPlayerName);
                
                const response = await axios.post('/game/create', {
                    player1_name: leftPlayerName,
                    player2_name: rightPlayerName
                });
                
                gameId = response.data.id;
                console.log('✅ Game created:', response.data);
                
                // Redirect ke game show (countdown akan otomatis start saat semua device ready)
                window.location.href = `/game/${gameId}`;
            } catch (error) {
                console.error('❌ Error creating game:', error);
            }
        }

        window.createGame = createGame;

        // Test function untuk debugging
        window.testPlayerJoin = function(side) {
            console.log('🧪 Testing player join for:', side);
            handlePlayerJoined({
                name: 'Test Player ' + (side === 'left' ? 'Left' : 'Right'),
                side: side
            });
        };
        
        console.log('💡 Tip: Ketik testPlayerJoin("left") atau testPlayerJoin("right") di console untuk test');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLobby);
    } else {
        initializeLobby();
    }
</script>
@endsection