<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DivisionController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'kepala') {
        return view('dashboard');
    }
    
    if ($user->division && $user->division->name === 'Customer Service') {
        return redirect()->route('jobs.create');
    }

    return redirect()->route('technician.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('divisions', DivisionController::class);
    Route::resource('users-management', UserController::class);

    Route::get('/jobs/history', [JobController::class, 'history'])->name('jobs.history');
    Route::post('/jobs/{job}/feedback', [JobController::class, 'storeFeedback'])->name('jobs.feedback');

    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    Route::get('/technician/dashboard', [JobController::class, 'technicianDashboard'])->name('technician.dashboard');
    Route::post('/jobs/{job}/accept', [JobController::class, 'acceptJob'])->name('jobs.accept');
    Route::post('/jobs/{job}/progress', [JobController::class, 'updateProgress'])->name('jobs.progress');
});

require __DIR__.'/auth.php';