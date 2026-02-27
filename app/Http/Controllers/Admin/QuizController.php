<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('quizzes')
            ->with(['quizzes' => function ($q) {
                $q->orderByDesc('created_at');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.quizzes.index', compact('subjects'));
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

        Quiz::create($validated);

        return redirect()->route('admin.quizzes.index')->with('success', 'Test muvaffaqiyatli yaratildi.');
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
}
