<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Guest sahifalar
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/daily/{date?}', [DailyLogController::class, 'show'])->name('daily.show');
    Route::post('/daily', [DailyLogController::class, 'store'])->name('daily.store');
    Route::post('/daily/toggle', [DailyLogController::class, 'toggle'])->name('daily.toggle');
    Route::post('/daily/custom-habit', [DailyLogController::class, 'addCustomHabit'])->name('daily.custom-habit');

    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    // TEMP DEBUG — delete after use
    Route::get('/debug-db', function() {
        return response()->json([
            'users_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
            'sessions_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('sessions'),
            'daily_logs_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('daily_logs'),
        ]);
    });
});
