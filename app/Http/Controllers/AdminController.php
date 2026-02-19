<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyLog;
use App\Models\DailyLogItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ActivityLog;
use App\Models\Feedback;

class AdminController extends Controller
{
    public function index()
    {
        // Simple admin check directly in controller for safety
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Bu sahifaga ruxsat yo\'q.');
        }

        $today = Carbon::today();

        // Key stats
        $totalUsers = User::count();
        $activeUsersToday = DailyLog::where('date', $today)->distinct('user_id')->count();
        $totalDeedsLogged = DailyLogItem::where('is_completed', true)->count();
        $maleUsers = User::where('gender', 'male')->count();
        $femaleUsers = User::where('gender', 'female')->count();
        $pendingFeedbackCount = Feedback::where('is_approved', false)->where('is_public', true)->count();

        // Recent users
        $recentUsers = User::latest()->take(10)->get();

        // Recent activity (last 20 logs)
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsersToday',
            'totalDeedsLogged',
            'maleUsers',
            'femaleUsers',
            'recentUsers',
            'recentActivity',
            'pendingFeedbackCount'
        ));
    }

    public function feedback()
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Ruvsat yo\'q.');
        }

        $feedbacks = Feedback::latest()->paginate(50);
        return view('admin.feedback', compact('feedbacks'));
    }

    public function approveFeedback(Feedback $feedback)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Ruvsat yo\'q'], 403);
        }

        $feedback->update(['is_approved' => true]);
        return back()->with('success', 'Fikr tasdiqlandi. Mashallah!');
    }

    public function deleteFeedback(Feedback $feedback)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Ruvsat yo\'q'], 403);
        }

        $feedback->delete();
        return back()->with('success', 'Fikr o\'chirildi.');
    }

    public function activity()
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Ruvsat yo\'q.');
        }

        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.activity', compact('activities'));
    }

    public function userShow(User $user)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Ruvsat yo\'q.');
        }

        $activities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->paginate(100);

        return view('admin.user_show', compact('user', 'activities'));
    }
}
