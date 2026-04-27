<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\HolidayController as ApiHolidayController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/fcm-token', function (Request $request) {
        $request->user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['success' => true]);
    });
    Route::get('/test-fcm-direct', function () {
    $user = \App\Models\User::whereNotNull('fcm_token')->first();
    
    if (!$user) {
        return response()->json(['error' => 'Tidak ada user dengan FCM token']);
    }

    $result = app(\App\Services\FcmPushService::class)->sendToToken(
        $user->fcm_token,
        'Test Background Notif',
        'Ini muncul saat app ditutup!',
        ['type' => 'test']
    );

    return response()->json([
        'success'    => $result,
        'user'       => $user->name,
        'fcm_token'  => substr($user->fcm_token, 0, 30) . '...',
    ]);
});
    Route::post('/logout',               [AuthApiController::class, 'logout']);
    Route::get('/user',                  fn(Request $r) => $r->user()->load('division'));
    Route::get('/users',                 [UserController::class, 'index']);
    Route::put('/user/change-password',  [App\Http\Controllers\Api\UserApiController::class, 'changePassword']);
    Route::get('/holidays', [ApiHolidayController::class, 'index']);
    Route::post('/presence/check-in',    [PresenceController::class, 'storeCheckIn']);
    Route::post('/presence/checkout',    [PresenceController::class, 'storeCheckOut']);
    Route::post('/presence/permissions', [PresenceController::class, 'storePermission']);
    Route::get('/presence/today',        [PresenceController::class, 'todayStatus']);
    Route::get('/presence/history',      [PresenceController::class, 'history']);
    Route::get('/attendance/config', [OfficeSettingController::class, 'getConfig']);
    Route::get('/chats',              [ChatController::class, 'index']);
    Route::post('/chats',             [ChatController::class, 'store']);
    Route::put('/chats/{id}',         [ChatController::class, 'update']);  
    Route::delete('/chats/{id}',      [ChatController::class, 'destroy']);  
    Route::post('/chats/{id}/pin',    [ChatController::class, 'pin']);      
    Route::post('/chats/{id}/unpin',  [ChatController::class, 'unpin']);   
    Route::post('/chats/{id}/seen',   [ChatController::class, 'markSeen']);
    Route::get('/chats/{id}/seen',    [ChatController::class, 'seenBy']);   

    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'unread_count'  => $request->user()->unreadNotifications->count(),
            'notifications' => $request->user()->notifications()->take(10)->get(),
        ]);
    });
    Route::get('/notifications/list',       [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
    Route::get('/notifications/count',      [NotificationController::class, 'getUnreadCount']);

    Route::get('/jobs/active',           [JobApiController::class, 'getActiveJobs']);
    Route::get('/jobs/history',          [JobApiController::class, 'getJobHistory']);
    Route::get('/jobs/technicians',      [JobApiController::class, 'getTechnicians']);
    Route::post('/jobs',                 [JobApiController::class, 'createJob']);
    Route::get('/jobs/{id}',             [JobApiController::class, 'show']);
    Route::post('/jobs/{id}/accept',     [JobApiController::class, 'acceptJob']);
    Route::post('/jobs/{id}/progress',   [JobApiController::class, 'updateProgress']);
    Route::post('/jobs/{id}/comments',   [JobApiController::class, 'addComment']);
});