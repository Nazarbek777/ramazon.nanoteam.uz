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

    // Dashboard (all admins, no permission check)
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard')
        ->middleware(\App\Http\Middleware\AdminMiddleware::class);

    // Protected Admin Routes
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // Subjects (granular)
        Route::get('subjects',          [SubjectController::class, 'index'])  ->name('subjects.index') ->middleware('permission:subjects.view');
        Route::get('subjects/create',   [SubjectController::class, 'create']) ->name('subjects.create')->middleware('permission:subjects.create');
        Route::post('subjects',         [SubjectController::class, 'store'])  ->name('subjects.store') ->middleware('permission:subjects.create');
        Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit')->middleware('permission:subjects.edit');
        Route::put('subjects/{subject}',      [SubjectController::class, 'update'])->name('subjects.update')->middleware('permission:subjects.edit');
        Route::delete('subjects/{subject}',   [SubjectController::class, 'destroy'])->name('subjects.destroy')->middleware('permission:subjects.delete');

        // Quizzes (granular)
        Route::get('quizzes',                           [QuizController::class, 'index'])       ->name('quizzes.index')        ->middleware('permission:quizzes.view');
        Route::get('quizzes/subject/{subject}',         [QuizController::class, 'showSubject']) ->name('quizzes.subject')      ->middleware('permission:quizzes.view');
        Route::get('quizzes/create',                    [QuizController::class, 'create'])      ->name('quizzes.create')       ->middleware('permission:quizzes.create');
        Route::post('quizzes',                          [QuizController::class, 'store'])       ->name('quizzes.store')        ->middleware('permission:quizzes.create');
        Route::get('quizzes/{quiz}/build',              [QuizController::class, 'buildQuiz'])   ->name('quizzes.build')        ->middleware('permission:quizzes.edit');
        Route::post('quizzes/{quiz}/sources',           [QuizController::class, 'storeSource']) ->name('quizzes.source.store') ->middleware('permission:quizzes.edit');
        Route::delete('quizzes/{quiz}/sources/{source}',[QuizController::class, 'deleteSource'])->name('quizzes.source.delete')->middleware('permission:quizzes.edit');
        Route::get('quizzes/{quiz}/edit',               [QuizController::class, 'edit'])        ->name('quizzes.edit')         ->middleware('permission:quizzes.edit');
        Route::put('quizzes/{quiz}',                    [QuizController::class, 'update'])      ->name('quizzes.update')       ->middleware('permission:quizzes.edit');
        Route::delete('quizzes/{quiz}',                 [QuizController::class, 'destroy'])     ->name('quizzes.destroy')      ->middleware('permission:quizzes.delete');

        // Questions (granular)
        Route::get('questions',                      [QuestionController::class, 'index'])       ->name('questions.index')        ->middleware('permission:questions.view');
        Route::get('questions/subject/{subject}',   [QuestionController::class, 'showSubject']) ->name('questions.subject')      ->middleware('permission:questions.view');
        Route::get('questions/create',               [QuestionController::class, 'create'])      ->name('questions.create')       ->middleware('permission:questions.create');
        Route::post('questions',                     [QuestionController::class, 'store'])       ->name('questions.store')        ->middleware('permission:questions.create');
        Route::get('questions/{question}/edit',      [QuestionController::class, 'edit'])        ->name('questions.edit')         ->middleware('permission:questions.edit');
        Route::put('questions/{question}',           [QuestionController::class, 'update'])      ->name('questions.update')       ->middleware('permission:questions.edit');
        Route::delete('questions/{question}',        [QuestionController::class, 'destroy'])     ->name('questions.destroy')      ->middleware('permission:questions.delete');

        // Broadcast
        Route::get('broadcast',      [App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index')->middleware('permission:broadcast.view');
        Route::post('broadcast/send',[App\Http\Controllers\Admin\BroadcastController::class, 'send']) ->name('broadcast.send') ->middleware('permission:broadcast.send');

        // Stats
        Route::get('stats',        [App\Http\Controllers\Admin\StatsController::class, 'index'])->name('stats.index')->middleware('permission:stats.view');
        Route::get('stats/{quiz}', [App\Http\Controllers\Admin\StatsController::class, 'show']) ->name('stats.show') ->middleware('permission:stats.view');

        // Users
        Route::get('users',                   [App\Http\Controllers\Admin\UserController::class, 'index'])  ->name('users.index')  ->middleware('permission:users.view');
        Route::post('users/{user}/block',     [App\Http\Controllers\Admin\UserController::class, 'block'])  ->name('users.block')  ->middleware('permission:users.block');
        Route::delete('users/{user}',         [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

        // Admin permissions (super_admin only — no permission middleware, controller returns 403 if not super_admin)
        Route::get('permissions',                   [App\Http\Controllers\Admin\AdminPermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions',                  [App\Http\Controllers\Admin\AdminPermissionController::class, 'store'])->name('permissions.store');
        Route::post('permissions/make-admin',       [App\Http\Controllers\Admin\AdminPermissionController::class, 'makeAdmin'])->name('permissions.make-admin');
        Route::post('permissions/create-admin',     [App\Http\Controllers\Admin\AdminPermissionController::class, 'createAdmin'])->name('permissions.create-admin');
        Route::post('permissions/{user}/remove',    [App\Http\Controllers\Admin\AdminPermissionController::class, 'removeAdmin'])->name('permissions.remove');
        Route::post('permissions/{user}/update',    [App\Http\Controllers\Admin\AdminPermissionController::class, 'updateAdmin'])->name('permissions.update');
    });
});


