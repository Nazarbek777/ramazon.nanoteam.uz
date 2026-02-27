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
     * Quiz builder — load bazalar (question banks) for the quiz's subject.
     */
    public function buildQuiz(Quiz $quiz)
    {
        $quiz->load(['sources.baza']);
        $bazalar = $this->getBazaTree($quiz->subject_id);
        return view('admin.quizzes.build', compact('quiz', 'bazalar'));
    }

    /** Recursively get all bazalar for a subject */
    private function getBazaTree(int $subjectId, ?int $parentId = null, int $depth = 0): \Illuminate\Support\Collection
    {
        $items = \App\Models\Baza::where('subject_id', $subjectId)
            ->where('parent_id', $parentId)
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        $result = collect();
        foreach ($items as $item) {
            $item->depth = $depth;
            $result->push($item);
            $result = $result->merge($this->getBazaTree($subjectId, $item->id, $depth + 1));
        }
        return $result;
    }

    /** Add a baza source to quiz */
    public function storeSource(Request $request, Quiz $quiz)
    {
        $request->validate([
            'baza_id' => 'required|exists:bazalar,id',
            'count'   => 'required|integer|min:1',
        ]);

        $available = \App\Models\Question::where('baza_id', $request->baza_id)->count();
        if ($request->count > $available && $available > 0) {
            return back()->with('error', "Bu bazada faqat {$available} ta savol bor.");
        }

        QuizSource::updateOrCreate(
            ['quiz_id' => $quiz->id, 'baza_id' => $request->baza_id],
            ['count'   => $request->count]
        );

        return back()->with('success', 'Baza qo\'shildi.');
    }

    /** Remove a source from quiz */
    public function deleteSource(Quiz $quiz, QuizSource $source)
    {
        $source->delete();
        return back()->with('success', 'Baza o\'chirildi.');
    }
}


