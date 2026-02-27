<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Baza;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;

class BazaController extends Controller
{
    /** Show all bazalar for a fan */
    public function index(Subject $subject)
    {
        $bazalar = $this->getTree($subject->id);
        return view('admin.bazalar.index', compact('subject', 'bazalar'));
    }

    /** Store a new baza */
    public function store(Request $request, Subject $subject)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:bazalar,id',
        ]);

        Baza::create([
            'subject_id' => $subject->id,
            'parent_id'  => $request->parent_id ?: null,
            'name'       => $request->name,
        ]);

        return back()->with('success', "\"$request->name\" bazasi qo'shildi.");
    }

    /** Delete a baza */
    public function destroy(Subject $subject, Baza $baza)
    {
        $baza->delete();
        return back()->with('success', 'Baza o\'chirildi.');
    }

    /** Update (rename) a baza */
    public function update(Request $request, Subject $subject, Baza $baza)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $baza->update(['name' => $request->name]);
        return back()->with('success', '"' . $request->name . '" bazasi yangilandi.');
    }

    /** Move a question to a different baza */
    public function moveQuestion(Request $request, Subject $subject, Baza $baza)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'target_baza_id' => 'required|exists:bazalar,id',
        ]);

        $question = Question::findOrFail($request->question_id);
        $question->update([
            'baza_id' => $request->target_baza_id,
        ]);

        return back()->with('success', 'Savol ko\'chirildi.');
    }

    /** Recursively get all bazalar for a subject with depth */
    private function getTree(int $subjectId, ?int $parentId = null, int $depth = 0): \Illuminate\Support\Collection
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
            $result = $result->merge($this->getTree($subjectId, $item->id, $depth + 1));
        }
        return $result;
    }
}
