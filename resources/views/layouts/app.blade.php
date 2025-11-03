{{-- filepath: e:\Project\tug_of_math\resources\views\layouts\app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tug of Math - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        
        .game-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .rope-animation {
            animation: swing 2s ease-in-out infinite;
        }

        @keyframes swing {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-10px); }
        }

        .player-card {
            transition: all 0.3s ease;
        }

        .player-card:hover {
            transform: scale(1.05);
        }

        .number-btn {
            transition: all 0.2s ease;
        }

        .number-btn:active {
            transform: scale(0.95);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Load Socket.IO v2 from cdnjs (reliable CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    
    <script>
        // Setup CSRF token
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        // Setup Laravel Echo with Socket.io v2
        window.Echo = new Echo({
            broadcaster: 'socket.io',
            host: '172.16.89.225:6001',
            transports: ['websocket', 'polling', 'flashsocket']
        });

        console.log('🔧 Echo initialized');
        
        window.Echo.connector.socket.on('connect', function() {
            console.log('✅ WebSocket CONNECTED! ID:', window.Echo.connector.socket.id);
        });

        window.Echo.connector.socket.on('disconnect', function(reason) {
            console.log('❌ WebSocket DISCONNECTED:', reason);
        });

        window.Echo.connector.socket.on('connect_error', function(error) {
            console.error('❌ Connection ERROR:', error);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
