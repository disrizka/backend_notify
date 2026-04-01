<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\UserController; // Pastikan ini benar

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());

    Route::post('/presence/check-in',    [PresenceController::class, 'storeCheckIn']);
    Route::post('/presence/checkout',    [PresenceController::class, 'storeCheckOut']);
    Route::post('/presence/permissions', [PresenceController::class, 'storePermission']);
    Route::get('/presence/today',        [PresenceController::class, 'todayStatus']);
    Route::get('/presence/history',       [PresenceController::class, 'history']);

    Route::get('/attendance/config', [OfficeSettingController::class, 'getConfig']);
    
    // Chat Routes (Cukup satu baris saja per rute)
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    
    // Rute User (Ini yang tadi bikin error)
    Route::get('/users', [UserController::class, 'index']);
});