<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\UserController; 

// Rute Publik
Route::post('/login', [AuthApiController::class, 'login']);

// Rute Terproteksi (Harus Login)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & User
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/users', [UserController::class, 'index']);

    // Presensi & Kehadiran
    Route::post('/presence/check-in',     [PresenceController::class, 'storeCheckIn']);
    Route::post('/presence/checkout',     [PresenceController::class, 'storeCheckOut']);
    Route::post('/presence/permissions', [PresenceController::class, 'storePermission']);
    Route::get('/presence/today',         [PresenceController::class, 'todayStatus']);
    Route::get('/presence/history',       [PresenceController::class, 'history']);

    // Konfigurasi Kantor
    Route::get('/attendance/config', [OfficeSettingController::class, 'getConfig']);
    
    // Chat Internal
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);

    // --- FITUR NOTIFIKASI ---
    
    // 1. Untuk Badge (Jumlah)
    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications->count(),
            'notifications' => $request->user()->notifications()->take(10)->get()
        ]);
    });

    // 2. Untuk Daftar di Halaman Notifikasi (PASTIKAN INI TERBACA)
    Route::get('/notifications/list', [NotificationController::class, 'index']);
    
    // 3. Untuk Tandai Dibaca
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);

    Route::put('/user/change-password', [App\Http\Controllers\Api\UserApiController::class, 'changePassword']);

    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount']);
});