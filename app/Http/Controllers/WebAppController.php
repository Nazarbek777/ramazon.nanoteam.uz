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

    public function showQuiz(Quiz $quiz)
    {
        $quiz->load(['subject', 'subject.questions.options']);
        
        // Shuffle questions if needed
        $questions = $quiz->subject->questions;
        if ($quiz->is_random) {
            $questions = $questions->shuffle();
        }

        return Inertia::render('QuizSession', [
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $answers = $request->input('answers'); // [question_id => option_id]
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

        $attempt = \App\Models\QuizAttempt::create([
            'user_id' => auth()->id() ?? 1, // Default to 1 for testing if not auth
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'started_at' => now(), // Should ideally come from request or session
            'completed_at' => now(),
        ]);

        return Inertia::render('Result', [
            'attempt' => $attempt->load('quiz'),
            'quiz' => $quiz
        ]);
    }
}
