<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\UserController;

// Rute Publik
Route::post('/login', [AuthApiController::class, 'login']);

// Rute Terproteksi
Route::middleware('auth:sanctum')->group(function () {

    // Auth & User
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/users', [UserController::class, 'index']);

    // Presensi
    Route::post('/presence/check-in',    [PresenceController::class, 'storeCheckIn']);
    Route::post('/presence/checkout',    [PresenceController::class, 'storeCheckOut']);
    Route::post('/presence/permissions', [PresenceController::class, 'storePermission']);
    Route::get('/presence/today',        [PresenceController::class, 'todayStatus']);
    Route::get('/presence/history',      [PresenceController::class, 'history']);

    // Konfigurasi Kantor
    Route::get('/attendance/config', [OfficeSettingController::class, 'getConfig']);

    // Chat
    Route::get('/chats',  [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);

    // Notifikasi
    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'unread_count'  => $request->user()->unreadNotifications->count(),
            'notifications' => $request->user()->notifications()->take(10)->get()
        ]);
    });
    Route::get('/notifications/list',     [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
    Route::get('/notifications/count',    [NotificationController::class, 'getUnreadCount']);

    Route::put('/user/change-password', [App\Http\Controllers\Api\UserApiController::class, 'changePassword']);

    // Technician: lihat & update tugas
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/jobs/active', [JobApiController::class, 'getActiveJobs']);
    Route::get('/jobs/history', [JobApiController::class, 'getJobHistory']);
    Route::get('/jobs/technicians', [JobApiController::class, 'getTechnicians']);
    Route::post('/jobs', [JobApiController::class, 'createJob']);
    Route::post('/jobs/{id}/accept', [JobApiController::class, 'acceptJob']);
    Route::post('/jobs/{id}/progress', [JobApiController::class, 'updateProgress']);
    Route::post('/jobs/{id}/comments', [JobApiController::class, 'addComment']);
});
});