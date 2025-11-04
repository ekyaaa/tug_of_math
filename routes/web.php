<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [GameController::class, 'index'])->name('home');
Route::get('/game/lobby', [GameController::class, 'lobby'])->name('game.lobby');

// Game routes
Route::post('/game/create', [GameController::class, 'createGame'])->name('game.create');
Route::post('/game/start-countdown', [GameController::class, 'startCountdown'])->name('game.startCountdown');
Route::post('/game/device-ready', [GameController::class, 'deviceReady'])->name('game.deviceReady');
Route::get('/game/{game}', [GameController::class, 'show'])->name('game.show');

// QR Code generation
Route::get('/qr/{side}', [GameController::class, 'generateQrPlayer'])->name('qr.generate');

// Player routes (untuk controller/HP)
Route::get('/join/{side}', [PlayerController::class, 'showJoinPage'])->name('player.join');
Route::post('/join/{side}', [PlayerController::class, 'submitJoin'])->name('player.submitJoin');
Route::get('/player/{player}/controller', [PlayerController::class, 'showController'])->name('player.controller');
Route::get('/player/{player}/current-question', [PlayerController::class, 'getCurrentQuestion'])->name('player.currentQuestion');
Route::post('/player/{player}/answer', [PlayerController::class, 'submitAnswer'])->name('player.answer');
Route::post('/player/ready', [PlayerController::class, 'playerReady'])->name('player.ready');
