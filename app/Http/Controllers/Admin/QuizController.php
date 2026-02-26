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
        $quizzes = Quiz::with('subject')->latest()->paginate(10);
        return view('admin.quizzes.index', compact('quizzes'));
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
            'time_limit' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
            'is_random' => 'boolean',
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
            'time_limit' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
            'is_random' => 'boolean',
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
