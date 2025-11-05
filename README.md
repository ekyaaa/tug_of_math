# 🎮 Tug of Math

> A real-time multiplayer math game where two players compete in a tug-of-war style mathematical challenge. Built with Laravel and WebSocket technology for seamless real-time interactions.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue?style=flat-square&logo=php)
![Socket.IO](https://img.shields.io/badge/Socket.IO-2.3.0-black?style=flat-square&logo=socket.io)
![Redis](https://img.shields.io/badge/Redis-Cache-red?style=flat-square&logo=redis)

## 📖 About

Tug of Math is an educational game that combines mathematics with competitive gameplay. Two players use their mobile devices as controllers to answer math questions while a central monitor displays the game progress in real-time. The game features:

- **Real-time gameplay** using WebSocket technology
- **3-device setup**: 1 monitor (game screen) + 2 mobile devices (controllers)
- **Dynamic math questions** with varying difficulty (addition, subtraction, multiplication, division)
- **QR code scanning** for quick player joining
- **Live score updates** and tug-of-war visualization

## 🎯 Game Features

### 🎲 Dynamic Question Generation
- Addition, subtraction, multiplication, and division
- Randomized numbers with difficulty scaling
- Smart answer validation
- Instant feedback on correct/incorrect answers

### 📱 Multi-Device Support
- **Monitor**: Displays game screen with QR codes, scores, and questions
- **Mobile Controllers**: Players input answers using custom number pad
- **Real-time sync**: All devices stay synchronized via WebSocket

## 🛠️ Tech Stack

### Backend
- **Laravel 10.x** - PHP Framework
- **Redis** - Message broker for broadcasting
- **MySQL/MariaDB** - Database
- **Laravel Broadcasting** - Event broadcasting system

### WebSocket Layer
- **Laravel Echo Server 1.6.3** - WebSocket server (Node.js)
- **Socket.IO v2.3.0** - Real-time communication
- **Redis Pub/Sub** - Message distribution

### Frontend
- **Tailwind CSS** - Utility-first CSS framework
- **Laravel Echo v1.11.3** - WebSocket client wrapper
- **Axios** - HTTP client
- **QRCode.js** - QR code generation

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- PHP >= 8.1
- Composer
- Node.js >= 14.x
- Redis Server
- MySQL/MariaDB
- Git

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ekyaaa/tug_of_math.git
cd tug_of_math
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install -g laravel-echo-server
```

### 4. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Environment Variables

Edit `.env` file:

```env
APP_NAME="Tug of Math"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tug_of_math
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 6. Database Setup

```bash
# Run migrations
php artisan migrate

# Optional: Seed database
php artisan db:seed
```

### 7. Start Redis Server

```bash
# Windows
redis-server

# Linux/Mac
sudo systemctl start redis
```

### 8. Start Laravel Echo Server

```bash
laravel-echo-server start
```

### 9. Start Laravel Development Server

```bash
php artisan serve
```

## 🎮 How to Play

### Setup (Monitor/PC)

1. Open browser and navigate to: `http://localhost:8000`
2. Click **"START GAME"**
3. You'll see the **Lobby** with two QR codes (Left & Right)

### Joining (Mobile Devices)

1. **Player 1**: Scan the **LEFT QR code** with your phone
2. **Player 2**: Scan the **RIGHT QR code** with your phone
3. Enter your name on your device
4. Wait for both players to join

### Starting the Game

1. Once both players have joined, the monitor will show:
   - Player avatars replacing QR codes
   - **"START GAME"** button becomes active
2. Click **"START GAME"** on the monitor
3. Math questions appear on both devices

### Playing

1. **Read the question** displayed on your mobile screen
2. **Enter your answer** using the number pad
3. **Submit** by tapping the green checkmark (✓)
4. **Watch the tug-of-war** animation on the monitor
5. First player to reach **10 correct answers wins**!

## 🔧 Configuration

### Laravel Echo Server

Edit `laravel-echo-server.json`:

```json
{
  "authHost": "http://127.0.0.1:8000",
  "authEndpoint": "/broadcasting/auth",
  "database": "redis",
  "databaseConfig": {
    "redis": {
      "port": "6379",
      "host": "127.0.0.1",
      "prefix": ""
    }
  },
  "devMode": true,
  "host": "0.0.0.0",
  "port": "6001",
  "protocol": "http",
  "socketio": {
    "pingTimeout": 60000,
    "pingInterval": 25000
  }
}
```

### Network Setup (LAN Play)

To play across devices on the same WiFi:

1. Find your PC's local IP address:
   ```bash
   # Windows
   ipconfig
   
   # Linux/Mac
   ifconfig
   ```

2. Update IP in `resources/views/layouts/app.blade.php`:
   ```javascript
   window.Echo = new Echo({
       broadcaster: 'socket.io',
       host: 'YOUR_IP:6001', // e.g., '192.168.1.100:6001'
       transports: ['websocket', 'polling', 'flashsocket']
   });
   ```

3. Access from mobile: `http://YOUR_IP:8000`

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- Laravel Framework
- Socket.IO
- Laravel Echo Server
- Tailwind CSS
- QRCode.js

---

Made with ❤️ and ☕ for educational purposes

**Happy Gaming!** 🎮✨
