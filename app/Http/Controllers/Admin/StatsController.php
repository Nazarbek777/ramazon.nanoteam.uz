<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::withCount('attempts')->latest()->paginate(10);
        return view('admin.stats.index', compact('quizzes'));
    }

    public function show(Quiz $quiz)
    {
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_attempts' => $attempts->total(),
            'avg_score' => QuizAttempt::where('quiz_id', $quiz->id)->avg('score'),
            'passed_count' => QuizAttempt::where('quiz_id', $quiz->id)->whereRaw('score >= (select pass_score from quizzes where id = ?)', [$quiz->id])->count(),
        ];

        return view('admin.stats.show', compact('quiz', 'attempts', 'stats'));
    }
}
