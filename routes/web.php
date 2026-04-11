<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\Admin\PresenceApprovalController;
use App\Http\Controllers\Admin\OfficeSettingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Web\ChatWebController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->role === 'kepala') return view('dashboard');
    if ($user->division && $user->division->name === 'Customer Service') return redirect()->route('jobs.create');
    return redirect()->route('technician.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/messages', [\App\Http\Controllers\Web\ChatWebController::class, 'index'])->name('admin.messages');
    Route::post('/messages/send', [\App\Http\Controllers\Api\ChatController::class, 'store'])->name('web.chats.store');


    // Rute Aksi Chat Web
    Route::post('/messages/{id}/pin', [\App\Http\Controllers\Api\ChatController::class, 'pin'])->name('web.chats.pin');
    Route::post('/messages/{id}/unpin', [\App\Http\Controllers\Api\ChatController::class, 'unpin'])->name('web.chats.unpin');
    Route::put('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'update'])->name('web.chats.update');
    Route::delete('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroy'])->name('web.chats.destroy');

    // ── Kehadiran & Absensi (Kepala only) ──────────────────────────────────
    Route::middleware('role:kepala')->group(function () {
        // Approval & Perizinan
        Route::get('/admin/attendance/approval', [PresenceApprovalController::class, 'index'])->name('admin.presence.index');
        Route::get('/admin/attendance/perizinan', [PresenceApprovalController::class, 'perizinan'])->name('admin.perizinan');
        Route::patch('/admin/attendance/approve/{id}', [PresenceApprovalController::class, 'leaveApprove'])->name('admin.presence.approve');
        Route::patch('/admin/attendance/reject/{id}', [PresenceApprovalController::class, 'leaveReject'])->name('admin.presence.reject');
        Route::post('/admin/attendance/approval/{id}/{status}', [PresenceApprovalController::class, 'updateStatus'])->name('admin.presence.updateStatus');
        
        // Jadwal Kerja (FullCalendar)
        Route::get('/admin/attendance/schedule', [PresenceApprovalController::class, 'schedule'])->name('admin.presence.schedule');
        Route::post('/admin/attendance/schedule/toggle', [PresenceApprovalController::class, 'toggleHoliday'])->name('admin.presence.toggle'); // FIX ERROR DISINI
        Route::post('/admin/attendance/schedule/update', [PresenceApprovalController::class, 'updateSchedule'])->name('admin.presence.updateSchedule');
        
        // Riwayat & Pengaturan
        Route::get('/admin/attendance/history', [PresenceApprovalController::class, 'history'])->name('admin.presence.history');
        Route::get('/admin/attendance/settings', [OfficeSettingController::class, 'index'])->name('admin.presence.settings');
        Route::post('/admin/attendance/settings/update', [OfficeSettingController::class, 'update'])->name('admin.presence.updateSettings');
    });

    // ── Division & User Management ──────────────────────────────────────────
    Route::resource('divisions', DivisionController::class);
    Route::resource('users-management', UserController::class);

    // ── Job System ───────────────────────────────────────────────────────────
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/history', [JobController::class, 'history'])->name('jobs.history');
    Route::post('/jobs/{job}/feedback', [JobController::class, 'storeFeedback'])->name('jobs.feedback');
    Route::post('/jobs/{job}/comment', [JobController::class, 'storeComment'])->name('jobs.comment');
    Route::delete('/job-comments/{comment}', [JobController::class, 'destroyComment'])->name('jobs.comment.destroy');

    // ── Technician System ────────────────────────────────────────────────────
    Route::get('/technician/dashboard', [JobController::class, 'technicianDashboard'])->name('technician.dashboard');
    Route::post('/jobs/{job}/accept', [JobController::class, 'acceptJob'])->name('jobs.accept');
    Route::post('/jobs/{job}/progress', [JobController::class, 'updateProgress'])->name('jobs.progress');

    // ── Checklist System ─────────────────────────────────────────────────────
    Route::get('/admin/checklists/create', [ChecklistController::class, 'createTemplate'])->name('admin.createTemplate');
    Route::post('/admin/checklists/store', [ChecklistController::class, 'storeTemplate'])->name('admin.storeTemplate');
    Route::get('/admin/checklists', [ChecklistController::class, 'indexTemplate'])->name('admin.indexTemplate');
    Route::get('/checklists', [ChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/fill/{type}/{date}', [ChecklistController::class, 'create'])->name('checklists.create');
    Route::post('/checklists/submit', [ChecklistController::class, 'storeAnswer'])->name('checklists.submit');
});

require __DIR__.'/auth.php';