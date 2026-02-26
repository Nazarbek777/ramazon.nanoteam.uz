<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\Option;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAppController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $subjects = Subject::with('quizzes')->get();

        // Get user's attempt statuses for all quizzes
        $quizStatuses = [];
        if ($userId) {
            $attempts = QuizAttempt::where('user_id', $userId)->get();
            foreach ($attempts as $attempt) {
                $quiz = Quiz::find($attempt->quiz_id);
                if ($attempt->completed_at) {
                    // Already completed
                    $quizStatuses[$attempt->quiz_id] = [
                        'status' => 'completed',
                        'score' => $attempt->score,
                        'correct_answers' => $attempt->correct_answers,
                        'total_questions' => $attempt->total_questions
                    ];
                } elseif ($quiz && $attempt->started_at) {
                    // Check if time expired
                    $limitSeconds = ($quiz->time_limit ?? 30) * 60;
                    $elapsed = now()->diffInSeconds($attempt->started_at);
                    if ($elapsed >= $limitSeconds) {
                        // Time expired - auto complete with 0
                        $attempt->update([
                            'score' => 0,
                            'completed_at' => $attempt->started_at->addSeconds($limitSeconds),
                        ]);
                        $quizStatuses[$attempt->quiz_id] = [
                            'status' => 'expired',
                            'score' => 0,
                        ];
                    } else {
                        $quizStatuses[$attempt->quiz_id] = [
                            'status' => 'in_progress',
                            'time_left' => $limitSeconds - $elapsed,
                        ];
                    }
                }
            }
        }

        return Inertia::render('Dashboard', [
            'subjects' => $subjects,
            'quizStatuses' => $quizStatuses,
        ]);
    }

    public function history()
    {
        $userId = Auth::id();
        $attempts = collect();

        if ($userId) {
            $attempts = QuizAttempt::where('user_id', $userId)
                ->whereNotNull('completed_at')
                ->with('quiz')
                ->orderByDesc('completed_at')
                ->get();
        }

        return Inertia::render('History', [
            'attempts' => $attempts,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $stats = [
            'total' => 0,
            'passed' => 0,
            'avg_score' => 0,
        ];

        if ($user) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->with('quiz')
                ->get();

            $stats['total'] = $attempts->count();
            $stats['passed'] = $attempts->filter(function ($a) {
                return $a->score >= ($a->quiz->pass_score ?? 70);
            })->count();
            $stats['avg_score'] = $stats['total'] > 0 ? round($attempts->avg('score')) : 0;
        }

        return Inertia::render('Profile', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $quiz = Quiz::where('access_code', $request->code)->first();

        if (!$quiz) {
            return back()->with('error', 'Kechirasiz, bunday ID bilan test topilmadi.');
        }

        return redirect()->route('webapp.quiz.show', $quiz->id);
    }

    public function showQuiz(Quiz $quiz)
    {
        try {
            $userId = Auth::id() ?? ((\App\Models\User::first())->id ?? 1);
            $quiz->load(['subject']);

            // 0) Check quiz schedule (starts_at / ends_at)
            $now = now();
            if ($quiz->starts_at && $quiz->starts_at > $now) {
                return Inertia::render('QuizBlocked', [
                    'quiz' => $quiz,
                    'type' => 'not_started',
                    'attempt' => null,
                    'message' => 'Bu test hali boshlanmagan. Boshlanish vaqti: ' . $quiz->starts_at->format('d.m.Y H:i'),
                ]);
            }

            if ($quiz->ends_at && $quiz->ends_at < $now) {
                return Inertia::render('QuizBlocked', [
                    'quiz' => $quiz,
                    'type' => 'expired',
                    'attempt' => null,
                    'message' => 'Bu testning muddati tugagan. Tugash vaqti: ' . $quiz->ends_at->format('d.m.Y H:i'),
                ]);
            }

            // 1) Check if user already COMPLETED this quiz
            $completedAttempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->whereNotNull('completed_at')
                ->latest()
                ->first();

            if ($completedAttempt) {
                return Inertia::render('QuizBlocked', [
                    'quiz' => $quiz,
                    'type' => 'completed',
                    'attempt' => $completedAttempt,
                    'message' => 'Siz bu testni allaqachon yechgansiz.',
                ]);
            }

            // 2) Check for active (in-progress) attempt
            $activeAttempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->whereNull('completed_at')
                ->latest()
                ->first();

            if ($activeAttempt) {
                // Check if time has expired
                $limitSeconds = ($quiz->time_limit ?? 30) * 60;
                $elapsed = now()->diffInSeconds($activeAttempt->started_at);

                if ($elapsed >= $limitSeconds) {
                    // Time expired — auto-complete attempt with current answers (score 0)
                    $activeAttempt->update([
                        'score' => 0,
                        'completed_at' => $activeAttempt->started_at->addSeconds($limitSeconds),
                    ]);

                    return Inertia::render('QuizBlocked', [
                        'quiz' => $quiz,
                        'type' => 'expired',
                        'attempt' => $activeAttempt,
                        'message' => 'Sizning bu testga vaqtingiz tugagan.',
                    ]);
                }

                // Time still left — resume the quiz
                $questions = $this->getQuestionsForQuiz($quiz);

                return Inertia::render('QuizSession', [
                    'quiz' => $quiz,
                    'questions' => $questions,
                    'startedAt' => $activeAttempt->started_at->toIso8601String(),
                    'attemptId' => $activeAttempt->id,
                ]);
            }

            // 3) No attempt exists — create new
            $questions = $this->getQuestionsForQuiz($quiz);

            $attempt = QuizAttempt::create([
                'user_id' => $userId,
                'quiz_id' => $quiz->id,
                'started_at' => now(),
                'total_questions' => $questions->count(),
            ]);

            return Inertia::render('QuizSession', [
                'quiz' => $quiz,
                'questions' => $questions,
                'startedAt' => $attempt->started_at->toIso8601String(),
                'attemptId' => $attempt->id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Quiz Show Error: ' . $e->getMessage());
            return redirect()->route('webapp.index')->with('error', 'Testni yuklashda xatolik: ' . $e->getMessage());
        }
    }

    private function getQuestionsForQuiz(Quiz $quiz)
    {
        if ($quiz->random_questions_count > 0) {
            $subjectIds = $this->getAllChildSubjectIds($quiz->subject_id);
            return Question::whereIn('subject_id', $subjectIds)
                ->with('options')
                ->inRandomOrder()
                ->limit($quiz->random_questions_count)
                ->get();
        }

        $quiz->load(['questions.options']);
        $questions = $quiz->questions;
        if ($quiz->is_random) {
            $questions = $questions->shuffle();
        }
        return $questions;
    }

    private function getAllChildSubjectIds($subjectId)
    {
        $ids = [$subjectId];
        $children = \App\Models\Subject::where('parent_id', $subjectId)->pluck('id');

        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getAllChildSubjectIds($childId));
        }

        return $ids;
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $answers = $request->input('answers', []);
        $attemptId = $request->input('attempt_id');
        $correctCount = 0;
        $totalQuestions = count($answers);

        foreach ($answers as $questionId => $optionId) {
            $isCorrect = Option::where('id', $optionId)
                ->where('question_id', $questionId)
                ->where('is_correct', true)
                ->exists();

            if ($isCorrect) {
                $correctCount++;
            }
        }

        $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;

        $attempt = QuizAttempt::find($attemptId);
        if ($attempt) {
            $attempt->update([
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'completed_at' => now(),
            ]);
        }

        return Inertia::render('Result', [
            'attempt' => $attempt->load('quiz'),
            'quiz' => $quiz
        ]);
    }
}
