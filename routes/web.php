<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/webapp', [App\Http\Controllers\WebAppController::class, 'index'])->name('webapp.index');
Route::get('/webapp/subject/{subject}', [App\Http\Controllers\WebAppController::class, 'showSubject'])->name('webapp.subject.show');
Route::get('/webapp/history', [App\Http\Controllers\WebAppController::class, 'history'])->name('webapp.history');
Route::get('/webapp/profile', [App\Http\Controllers\WebAppController::class, 'profile'])->name('webapp.profile');
Route::get('/webapp/attempt/{attempt}', [App\Http\Controllers\WebAppController::class, 'attemptDetail'])->name('webapp.attempt.detail');
Route::get('/webapp/quiz/{quiz}', [App\Http\Controllers\WebAppController::class, 'showQuiz'])->name('webapp.quiz.show');
Route::post('/webapp/quiz/join', [App\Http\Controllers\WebAppController::class, 'joinByCode'])->name('webapp.quiz.join');
Route::post('/webapp/quiz/{quiz}/start', [App\Http\Controllers\WebAppController::class, 'startQuiz'])->name('webapp.quiz.start');
Route::post('/webapp/quiz/{quiz}/submit', [App\Http\Controllers\WebAppController::class, 'submitQuiz'])->name('webapp.quiz.submit');
Route::post('/telegram/webhook', [App\Http\Controllers\TelegramBotController::class, 'handle'])->name('telegram.webhook');
Route::get('/debug/logs', [App\Http\Controllers\LogViewerController::class, 'index']);
Route::get('/debug/logs/clear', [App\Http\Controllers\LogViewerController::class, 'clear']);

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    // Dashboard redirect
    Route::get('/', function() { return redirect()->route('admin.subjects.index'); })->name('dashboard')
        ->middleware(\App\Http\Middleware\AdminMiddleware::class);

    // Protected Admin Routes
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::resource('subjects', SubjectController::class)->middleware('permission:subjects');
        Route::resource('questions', QuestionController::class)->middleware('permission:questions');
        Route::resource('quizzes', QuizController::class)->middleware('permission:quizzes');

        // Broadcast
        Route::get('broadcast', [App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index')->middleware('permission:broadcast');
        Route::post('broadcast/send', [App\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('broadcast.send')->middleware('permission:broadcast');

        // Stats
        Route::get('stats', [App\Http\Controllers\Admin\StatsController::class, 'index'])->name('stats.index')->middleware('permission:stats');
        Route::get('stats/{quiz}', [App\Http\Controllers\Admin\StatsController::class, 'show'])->name('stats.show')->middleware('permission:stats');

        // Users (user management)
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index')->middleware('permission:users');
        Route::post('users/{user}/block', [App\Http\Controllers\Admin\UserController::class, 'block'])->name('users.block')->middleware('permission:users');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users');

        // Admin permissions (super_admin only)
        Route::get('permissions', [App\Http\Controllers\Admin\AdminPermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions', [App\Http\Controllers\Admin\AdminPermissionController::class, 'store'])->name('permissions.store');
        Route::post('permissions/make-admin', [App\Http\Controllers\Admin\AdminPermissionController::class, 'makeAdmin'])->name('permissions.make-admin');
        Route::post('permissions/{user}/remove', [App\Http\Controllers\Admin\AdminPermissionController::class, 'removeAdmin'])->name('permissions.remove');
    });
});

