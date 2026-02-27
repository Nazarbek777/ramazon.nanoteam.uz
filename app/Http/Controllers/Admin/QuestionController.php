<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::withCount('questions')->orderBy('name')->get();
        $selectedSubject = null;
        $questions = collect();

        if ($request->subject_id) {
            $selectedSubject = Subject::find($request->subject_id);
            if ($selectedSubject) {
                $questions = $selectedSubject->questions()->withCount('options')->latest()->get();
            }
        }

        return view('admin.questions.index', compact('subjects', 'selectedSubject', 'questions'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('admin.questions.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'content' => 'required|string',
            'type' => 'required|in:single,multiple',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string',
            'correct_option' => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $question = Question::create([
                'subject_id' => $request->subject_id,
                'content' => $request->content,
                'type' => $request->type,
                'points' => $request->points,
            ]);

            foreach ($request->options as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'content' => $optionData['content'],
                    'is_correct' => $request->correct_option == $index,
                ]);
            }
        });

        return redirect()->route('admin.questions.index')->with('success', 'Savol muvaffaqiyatli qo\'shildi.');
    }

    public function edit(Question $question)
    {
        $subjects = Subject::all();
        $question->load('options');
        return view('admin.questions.edit', compact('question', 'subjects'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'content' => 'required|string',
            'type' => 'required|in:single,multiple',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string',
            'correct_option' => 'required',
        ]);

        DB::transaction(function () use ($request, $question) {
            $question->update([
                'subject_id' => $request->subject_id,
                'content' => $request->content,
                'type' => $request->type,
                'points' => $request->points,
            ]);

            // Simple way: delete old options and create new ones
            $question->options()->delete();

            foreach ($request->options as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'content' => $optionData['content'],
                    'is_correct' => $request->correct_option == $index,
                ]);
            }
        });

        return redirect()->route('admin.questions.index')->with('success', 'Savol muvaffaqiyatli yangilandi.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.questions.index')->with('success', 'Savol o\'chirib tashlandi.');
    }
}
