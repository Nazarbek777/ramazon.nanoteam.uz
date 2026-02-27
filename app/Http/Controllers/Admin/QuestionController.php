<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Baza;
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
        return view('admin.questions.index', compact('subjects'));
    }

    /** Show bazalar list for a subject (questions flow entry) */
    public function showSubject(Subject $subject)
    {
        $bazalar = $this->getBazaTree($subject->id);
        return view('admin.questions.subject', compact('subject', 'bazalar'));
    }

    /** Show questions inside a specific baza */
    public function showBaza(Subject $subject, Baza $baza)
    {
        $baza->load('parent');
        $questions = Question::where('baza_id', $baza->id)
            ->withCount('options')
            ->latest()
            ->get();
        $childBazalar = Baza::where('parent_id', $baza->id)->withCount('questions')->get();
        return view('admin.questions.baza', compact('subject', 'baza', 'questions', 'childBazalar'));
    }

    /** Recursively get all bazalar for a subject with depth */
    private function getBazaTree(int $subjectId, ?int $parentId = null, int $depth = 0): \Illuminate\Support\Collection
    {
        $items = Baza::where('subject_id', $subjectId)
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

    public function create()
    {
        $subjects = Subject::all();
        return view('admin.questions.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'baza_id'    => 'nullable|exists:bazalar,id',
            'content'    => 'required|string',
            'type'       => 'required|in:single,multiple',
            'points'     => 'required|integer|min:1',
            'options'    => 'required|array|min:2',
            'options.*.content' => 'required|string',
            'correct_option' => 'required',
        ]);

        $bazaId = $request->baza_id ?: null;

        DB::transaction(function () use ($request, $bazaId) {
            $question = Question::create([
                'subject_id' => $request->subject_id,
                'baza_id'    => $bazaId,
                'content'    => $request->content,
                'type'       => $request->type,
                'points'     => $request->points,
            ]);

            foreach ($request->options as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'content'     => $optionData['content'],
                    'is_correct'  => $request->correct_option == $index,
                ]);
            }
        });

        // Redirect back to baza if came from baza
        if ($bazaId) {
            $baza = Baza::find($bazaId);
            return redirect()
                ->route('admin.questions.baza', [$baza->subject_id, $bazaId])
                ->with('success', 'Savol qo\'shildi.');
        }

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
