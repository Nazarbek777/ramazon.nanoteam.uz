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

    // Google Login
    Route::get('/auth/google', [App\Http\Controllers\Auth\SocialController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [App\Http\Controllers\Auth\SocialController::class, 'handleGoogleCallback']);
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

    // FEEDBACK
    Route::get('/feedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');

    // ADMIN DASHBOARD
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index')->middleware('auth');
    Route::get('/admin/activity', [App\Http\Controllers\AdminController::class, 'activity'])->name('admin.activity')->middleware('auth');
    Route::get('/admin/user/{user}', [App\Http\Controllers\AdminController::class, 'userShow'])->name('admin.user.show')->middleware('auth');
    Route::get('/admin/feedback', [App\Http\Controllers\AdminController::class, 'feedback'])->name('admin.feedback')->middleware('auth');
    Route::post('/admin/feedback/{feedback}/approve', [App\Http\Controllers\AdminController::class, 'approveFeedback'])->name('admin.feedback.approve')->middleware('auth');
    Route::post('/admin/feedback/{feedback}/delete', [App\Http\Controllers\AdminController::class, 'deleteFeedback'])->name('admin.feedback.delete')->middleware('auth');

// FORCE MIGRATION — because artisan doesn't work
Route::get('/migrate-activity', function() {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            \Illuminate\Support\Facades\Schema::create('activity_logs', function ($table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('action')->nullable();
                $table->string('path');
                $table->string('method');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();
            });
            return "Muvaffaqiyatli: 'activity_logs' jadvali qo'shildi. Mashallah!";
        }
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_seen_at')) {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->timestamp('last_seen_at')->nullable();
                $table->string('google_id')->nullable()->after('id');
                $table->string('avatar')->nullable()->after('email');
            });
            return "Muvaffaqiyatli: 'last_seen_at' va Google ustunlari qo'shildi. Mashallah!";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('feedback')) {
            \Illuminate\Support\Facades\Schema::create('feedback', function ($table) {
                $table->id();
                $table->text('content');
                $table->boolean('is_public')->default(false);
                $table->boolean('is_approved')->default(false);
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
            return "Muvaffaqiyatli: 'feedback' jadvali qo'shildi. Mashallah!";
        }

        return "Allaqachon qo'shilgan.";
    } catch (\Exception $e) {
        return "Xato: " . $e->getMessage();
    }
});

// FORCE MIGRATION — because artisan doesn't work
Route::get('/migrate-admin', function() {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_admin')) {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->boolean('is_admin')->default(false)->after('gender');
            });
            return "Muvaffaqiyatli: 'is_admin' ustuni qo'shildi. <a href='/make-me-admin'>Endi admin huquqini oling</a>";
        }
        return "Allaqachon qo'shilgan.";
    } catch (\Exception $e) {
        return "Xato: " . $e->getMessage();
    }
});

// TEMP SETUP — give admin access to currently logged in user
Route::get('/make-me-admin', function() {
    $user = Auth::user();
    if ($user) {
        $user->update(['is_admin' => true]);
        return "Muborak! Endi siz adminsiz. <a href='/admin'>Admin Panelga o'ting</a>";
    }
    return "Avval tizimga kiring.";
})->middleware('auth');

    // TEMP DEBUG — delete after use
    Route::get('/debug-db', function() {
        return response()->json([
            'users_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
            'sessions_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('sessions'),
            'daily_logs_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('daily_logs'),
        ]);
    });
});
