<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Inertia\Inertia;
use Illuminate\Http\Request;

class WebAppController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('quizzes')->get();
        return Inertia::render('Dashboard', [
            'subjects' => $subjects
        ]);
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $quiz = \App\Models\Quiz::where('access_code', $request->code)->first();

        if (!$quiz) {
            return back()->with('error', 'Kechirasiz, bunday ID bilan test topilmadi.');
        }

        return redirect()->route('webapp.quiz.show', $quiz->id);
    }

    public function showQuiz(\App\Models\Quiz $quiz)
    {
        $quiz->load(['subject']);
        
        // Find active attempt or create new
        $userId = auth()->id() ?? 1; // Fallback for dev
        $attempt = \App\Models\QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if (!$attempt) {
            $attempt = \App\Models\QuizAttempt::create([
                'user_id' => $userId,
                'quiz_id' => $quiz->id,
                'started_at' => now(),
            ]);
        }

        $questions = collect();

        if ($quiz->random_questions_count > 0) {
            // Get questions from this subject and all its sub-subjects
            $subjectIds = $this->getAllChildSubjectIds($quiz->subject_id);
            $questions = \App\Models\Question::whereIn('subject_id', $subjectIds)
                ->with('options')
                ->inRandomOrder()
                ->limit($quiz->random_questions_count)
                ->get();
        } else {
            // Use manually attached questions
            $quiz->load(['questions.options']);
            $questions = $quiz->questions;
            if ($quiz->is_random) {
                $questions = $questions->shuffle();
            }
        }

        return Inertia::render('QuizSession', [
            'quiz' => $quiz,
            'questions' => $questions,
            'startedAt' => $attempt->started_at->toIso8601String(),
            'attemptId' => $attempt->id
        ]);
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

    public function submitQuiz(Request $request, \App\Models\Quiz $quiz)
    {
        $answers = $request->input('answers'); // [question_id => option_id]
        $attemptId = $request->input('attempt_id');
        $correctCount = 0;
        $totalQuestions = count($answers);

        foreach ($answers as $questionId => $optionId) {
            $isCorrect = \App\Models\Option::where('id', $optionId)
                ->where('question_id', $questionId)
                ->where('is_correct', true)
                ->exists();
            
            if ($isCorrect) {
                $correctCount++;
            }
        }

        $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;

        $attempt = \App\Models\QuizAttempt::find($attemptId);
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
