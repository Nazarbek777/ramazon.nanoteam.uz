<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user')
            ->orWhereNull('role')
            ->withCount('attempts')
            ->orderByDesc('created_at');

        if ($request->search) {
            $q = $request->search;
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                   ->orWhere('phone_number', 'like', "%$q%")
                   ->orWhere('telegram_id', 'like', "%$q%");
            });
        }

        $users = $query->paginate(30)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function block(User $user)
    {
        $user->update(['is_blocked' => !$user->is_blocked]);
        $status = $user->is_blocked ? 'Bloklandi' : 'Blok ochildi';
        return back()->with('success', "{$user->name}: {$status}");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Foydalanuvchi o\'chirildi');
    }
}
