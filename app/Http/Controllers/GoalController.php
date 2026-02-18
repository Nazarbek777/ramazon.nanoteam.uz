<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $goals = Goal::where('user_id', $user->id)->with('habit')->get();
        $habits = Habit::forUser($user->id)->orderBy('sort_order')->get();

        return view('goals.index', compact('goals', 'habits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'target_value' => 'required|numeric|min:1',
            'unit' => 'required|string|max:50',
            'habit_id' => 'nullable|exists:habits,id',
        ]);

        Goal::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'target_value' => $request->target_value,
            'current_value' => 0,
            'unit' => $request->unit,
            'habit_id' => $request->habit_id,
        ]);

        return redirect()->route('goals.index')
            ->with('success', 'Yangi maqsad qo\'shildi! 🎯');
    }

    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) abort(403);

        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Maqsad o\'chirildi.');
    }
}
