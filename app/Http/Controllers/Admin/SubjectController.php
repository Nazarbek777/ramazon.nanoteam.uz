<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Baza;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::latest()->paginate(10);
        return view('admin.subjects.index', compact('subjects'));
    }

    /** Subject detail page — manage bazalar */
    public function show(Subject $subject)
    {
        $bazalar = $this->getBazaTree($subject->id);
        return view('admin.subjects.show', compact('subject', 'bazalar'));
    }

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
        $subjects = Subject::whereNull('parent_id')->get();
        return view('admin.subjects.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:subjects,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Fan muvaffaqiyatli qo\'shildi.');
    }

    public function edit(Subject $subject)
    {
        $subjects = Subject::whereNull('parent_id')->where('id', '!=', $subject->id)->get();
        return view('admin.subjects.edit', compact('subject', 'subjects'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:subjects,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Fan muvaffaqiyatli yangilandi.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Fan o\'chirib tashlandi.');
    }
}
