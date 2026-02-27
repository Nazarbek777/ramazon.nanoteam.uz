<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSource;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $subjects = Subject::withCount('quizzes')->orderBy('name')->get();
        return view('admin.quizzes.index', compact('subjects'));
    }

    public function showSubject(Subject $subject)
    {
        $quizzes = $subject->quizzes()->withCount('sources')->orderByDesc('created_at')->get();
        return view('admin.quizzes.subject', compact('subject', 'quizzes'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('admin.quizzes.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'access_code' => 'nullable|string|max:50|unique:quizzes,access_code',
            'time_limit' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
            'is_random' => 'required|boolean',
            'random_questions_count' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $quiz = Quiz::create($validated);

        // Redirect to quiz builder page to configure sources (bazalar)
        return redirect()->route('admin.quizzes.build', $quiz)
            ->with('success', 'Test yaratildi. Endi bazalarni qo\'shing.');
    }

    public function edit(Quiz $quiz)
    {
        $subjects = Subject::all();
        return view('admin.quizzes.edit', compact('quiz', 'subjects'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'access_code' => 'nullable|string|max:50|unique:quizzes,access_code,' . $quiz->id,
            'time_limit' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
            'is_random' => 'required|boolean',
            'random_questions_count' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.index')->with('success', 'Test muvaffaqiyatli yangilandi.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Test o\'chirib tashlandi.');
    }

    /**
     * Quiz builder — shows subject's child subjects (bazalar) so admin
     * can pick how many questions to pull from each.
     */
    public function buildQuiz(Quiz $quiz)
    {
        $quiz->load(['subject.children', 'sources.subject']);

        // All child subjects (bazalar) of this quiz's subject
        $bazalar = $quiz->subject->children ?? collect();

        // If parent subject has no children, show the subject itself as the only baza
        if ($bazalar->isEmpty()) {
            $bazalar = collect([$quiz->subject]);
        }

        // Add question counts to each baza
        $bazalar->each(function ($baza) {
            $baza->questions_count = $baza->questions()->count();
        });

        return view('admin.quizzes.build', compact('quiz', 'bazalar'));
    }

    /**
     * Add a source (baza + count) to quiz.
     */
    public function storeSource(Request $request, Quiz $quiz)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'count'      => 'required|integer|min:1',
        ]);

        // Check if baza has enough questions
        $available = \App\Models\Question::where('subject_id', $request->subject_id)->count();
        if ($request->count > $available) {
            return back()->with('error', "Bu bazada faqat {$available} ta savol bor. {$request->count} ta so'raldingiz.");
        }

        QuizSource::updateOrCreate(
            ['quiz_id' => $quiz->id, 'subject_id' => $request->subject_id],
            ['count'   => $request->count]
        );

        return back()->with('success', 'Baza qo\'shildi.');
    }

    /**
     * Remove a source from quiz.
     */
    public function deleteSource(Quiz $quiz, QuizSource $source)
    {
        $source->delete();
        return back()->with('success', 'Baza o\'chirildi.');
    }
}

