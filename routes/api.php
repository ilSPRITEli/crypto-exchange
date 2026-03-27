<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile', [AuthController::class, 'profile']);

Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

Route::get('/trades', [TradeController::class, 'index']);
Route::get('/trades/{id}', [TradeController::class, 'show']);
Route::post('/trades', [TradeController::class, 'store']);
Route::post('/trades/{id}/complete', [TradeController::class, 'complete']);
Route::post('/trades/{id}/cancel', [TradeController::class, 'cancel']);

Route::post('/transfers/internal', [TransferController::class, 'internalTransfer']);
Route::post('/transfers/external', [TransferController::class, 'externalTransfer']);
Route::get('/transfers/history', [TransferController::class, 'history']);

Route::get('/wallets', [WalletController::class, 'index']);
Route::get('/wallets/user/{userId}', [WalletController::class, 'showByUser']);

