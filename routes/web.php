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
Route::get('/webapp/quiz/{quiz}', [App\Http\Controllers\WebAppController::class, 'showQuiz'])->name('webapp.quiz.show');
Route::post('/webapp/quiz/join', [App\Http\Controllers\WebAppController::class, 'joinByCode'])->name('webapp.quiz.join');
Route::post('/webapp/quiz/{quiz}/submit', [App\Http\Controllers\WebAppController::class, 'submitQuiz'])->name('webapp.quiz.submit');
Route::post('/telegram/webhook', [App\Http\Controllers\TelegramBotController::class, 'handle'])->name('telegram.webhook');
Route::get('/debug/logs', [App\Http\Controllers\LogViewerController::class, 'index']);
Route::get('/debug/logs/clear', [App\Http\Controllers\LogViewerController::class, 'clear']);

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::resource('subjects', SubjectController::class);
        Route::resource('questions', QuestionController::class);
        Route::resource('quizzes', QuizController::class);
        
        // Broadcast
        Route::get('broadcast', [App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('broadcast', [App\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('broadcast.send');
    });
});
