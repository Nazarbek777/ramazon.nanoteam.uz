<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\Option;
use App\Helpers\BotLogger;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function showSubject(Subject $subject)
    {
        $userId = Auth::id();
        $subject->load('quizzes');

        $quizStatuses = [];
        if ($userId) {
            $quizIds = $subject->quizzes->pluck('id');
            $attempts = QuizAttempt::where('user_id', $userId)
                ->whereIn('quiz_id', $quizIds)
                ->get();

            foreach ($attempts as $attempt) {
                $quiz = $subject->quizzes->firstWhere('id', $attempt->quiz_id);
                if ($attempt->completed_at) {
                    $quizStatuses[$attempt->quiz_id] = [
                        'status' => 'completed',
                        'score' => $attempt->score,
                        'correct_answers' => $attempt->correct_answers,
                        'total_questions' => $attempt->total_questions,
                    ];
                } elseif ($quiz && $attempt->started_at) {
                    $limitSeconds = ($quiz->time_limit ?? 30) * 60;
                    $elapsed = now()->diffInSeconds($attempt->started_at);
                    if ($elapsed >= $limitSeconds) {
                        $attempt->update(['score' => 0, 'completed_at' => $attempt->started_at->addSeconds($limitSeconds)]);
                        $quizStatuses[$attempt->quiz_id] = ['status' => 'expired', 'score' => 0];
                    } else {
                        $quizStatuses[$attempt->quiz_id] = ['status' => 'in_progress', 'time_left' => $limitSeconds - $elapsed];
                    }
                }
            }
        }

        return Inertia::render('SubjectQuizzes', [
            'subject' => $subject,
            'quizzes' => $subject->quizzes,
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
                ->with(['quiz.subject'])
                ->orderByDesc('completed_at')
                ->get();
        }

        $bySubject = $attempts->groupBy(function($a) {
            return optional(optional($a->quiz)->subject)->name ?? 'Boshqa';
        })->map(fn($g) => $g->values());

        return Inertia::render('History', [
            'attempts' => $attempts,
            'bySubject' => $bySubject,
        ]);
    }

    public function attemptDetail(QuizAttempt $attempt)
    {
        $attempt->load([
            'quiz.subject',
            'answers.question.options',
            'answers.option',
        ]);

        return Inertia::render('AttemptDetail', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
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

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        try {
            $userId = Auth::id() ?? ((\App\Models\User::first())->id ?? 1);
            $answersInput = $request->input('answers', []);
            $attemptId = $request->input('attempt_id');

            $attempt = QuizAttempt::where('id', $attemptId)
                ->where('user_id', $userId)
                ->whereNull('completed_at')
                ->firstOrFail();

            $questionIds = array_keys($answersInput);
            $questions = Question::whereIn('id', $questionIds)->with('options')->get()->keyBy('id');

            $correctCount = 0;
            foreach ($answersInput as $questionId => $optionId) {
                $question = $questions->get($questionId);
                if (!$question) continue;

                $isCorrect = $question->options
                    ->where('id', $optionId)
                    ->where('is_correct', true)
                    ->isNotEmpty();

                if ($isCorrect) $correctCount++;

                \App\Models\AttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id'     => $questionId,
                    'option_id'       => $optionId,
                    'is_correct'      => $isCorrect,
                ]);
            }

            $total = $attempt->total_questions ?: max(count($answersInput), 1);
            $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

            $attempt->update([
                'correct_answers'  => $correctCount,
                'score'            => $score,
                'completed_at'     => now(),
                'total_questions'  => $total,
            ]);

            return Inertia::render('Result', [
                'attempt' => $attempt->fresh(),
                'quiz'    => $quiz,
            ]);

        } catch (\Exception $e) {
            \Log::error('Submit error: ' . $e->getMessage());
            return redirect()->route('webapp.index')->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function showQuiz(Quiz $quiz)
    {
        try {
            $userId = Auth::id() ?? ((\App\Models\User::first())->id ?? 1);
            $quiz->load(['subject']);

            // 0a) Check if user is blocked
            $currentUser = \App\Models\User::find($userId);
            if ($currentUser && $currentUser->isBlocked()) {
                return Inertia::render('QuizBlocked', [
                    'quiz'    => $quiz,
                    'type'    => 'blocked',
                    'attempt' => null,
                    'message' => 'Kechirasiz, sizning hisobingiz bloklangan. Admin bilan bog\'laning: @abdullayevna_jamoa',
                ]);
            }

            // 0b) Check quiz schedule (starts_at / ends_at)
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

                // Time still left — resume the quiz using SAVED question order
                $questions = $this->getQuestionsInOrder($activeAttempt, $quiz);

                return Inertia::render('QuizSession', [
                    'quiz' => $quiz,
                    'questions' => $questions,
                    'startedAt' => $activeAttempt->started_at->toIso8601String(),
                    'attemptId' => $activeAttempt->id,
                ]);
            }

            // 3) No attempt exists — show intro/confirmation screen
            return Inertia::render('QuizIntro', [
                'quiz' => $quiz,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('webapp.index')->with('error', 'Xatolik yuz berdi.');
        }
    }

    public function startQuiz(Quiz $quiz)
    {
        try {
            $userId = Auth::id() ?? ((\App\Models\User::first())->id ?? 1);
            $quiz->load(['subject']);

            // Guard: already completed?
            $completed = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->whereNotNull('completed_at')
                ->exists();

            if ($completed) {
                return redirect()->route('webapp.quiz.show', $quiz->id);
            }

            // Guard: already in progress?
            $active = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->whereNull('completed_at')
                ->first();

            if ($active) {
                return redirect()->route('webapp.quiz.show', $quiz->id);
            }

            // Create attempt now — save question order so reload is consistent
            $questions = $this->getQuestionsForQuiz($quiz);
            $questionIds = $questions->pluck('id')->toArray();

            QuizAttempt::create([
                'user_id'          => $userId,
                'quiz_id'          => $quiz->id,
                'started_at'       => now(),
                'total_questions'  => $questions->count(),
                'questions_order'  => $questionIds,
            ]);

            // Redirect to GET route so reload doesn't cause 405
            return redirect()->route('webapp.quiz.show', $quiz->id);

        } catch (\Exception $e) {
            \Log::error('Quiz Show Error: ' . $e->getMessage());
            return redirect()->route('webapp.index')->with('error', 'Testni yuklashda xatolik: ' . $e->getMessage());
        }
    }

    private function getQuestionsForQuiz(Quiz $quiz)
    {
        // 1) If quiz has configured baza sources — use them
        $sources = $quiz->sources()->with('baza')->get();
        if ($sources->isNotEmpty()) {
            $questions = collect();
            foreach ($sources as $source) {
                if (!$source->baza) continue;
                $picked = Question::where('baza_id', $source->baza_id)
                    ->with(['options' => fn($q) => $q->orderBy('id')])
                    ->inRandomOrder()
                    ->limit($source->count)
                    ->get();
                $questions = $questions->merge($picked);
            }
            return $questions->shuffle();
        }

        // 2) Legacy: random from subject
        if ($quiz->random_questions_count > 0) {
            $subjectIds = $this->getAllChildSubjectIds($quiz->subject_id);
            return Question::whereIn('subject_id', $subjectIds)
                ->with(['options' => fn($q) => $q->orderBy('id')])
                ->inRandomOrder()
                ->limit($quiz->random_questions_count)
                ->get();
        }

        // 3) Manually assigned questions via quiz_questions pivot
        $quiz->load(['questions' => fn($q) => $q->orderBy('id'),
                     'questions.options' => fn($q) => $q->orderBy('id')]);
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

    private function getQuestionsInOrder(QuizAttempt $attempt, Quiz $quiz)
    {
        $order = $attempt->questions_order;

        // No saved order (old attempts) — fall back to fresh load
        if (empty($order)) {
            return $this->getQuestionsForQuiz($quiz);
        }

        // Load questions in the exact saved order, options always sorted by id
        $questions = Question::whereIn('id', $order)
            ->with(['options' => fn($q) => $q->orderBy('id')])
            ->get()
            ->keyBy('id');

        // Return in saved order
        return collect($order)->map(fn($id) => $questions->get($id))->filter()->values();
    }
}

