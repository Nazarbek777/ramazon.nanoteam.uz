<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminPermission;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    // All available pages that can be permissioned
    public const PAGES = [
        'subjects'   => 'Fanlar',
        'quizzes'    => 'Testlar',
        'questions'  => 'Savollar',
        'stats'      => 'Statistika',
        'broadcast'  => 'Xabar yuborish',
        'users'      => 'Foydalanuvchilar',
    ];

    public function index()
    {
        $admins = User::where('role', 'admin')->with('permissions')->get();
        return view('admin.permissions.index', [
            'admins' => $admins,
            'pages'  => self::PAGES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'admin_id'    => 'required|exists:users,id',
            'permissions' => 'array',
        ]);

        $admin = User::findOrFail($request->admin_id);

        // Sync permissions
        $admin->permissions()->delete();
        foreach ($request->permissions ?? [] as $page) {
            if (array_key_exists($page, self::PAGES)) {
                AdminPermission::create(['admin_id' => $admin->id, 'page' => $page]);
            }
        }

        return back()->with('success', "{$admin->name} uchun ruxsatlar saqlandi.");
    }

    public function makeAdmin(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->user_id);
        $user->update(['role' => 'admin']);
        return back()->with('success', "{$user->name} admin qilindi.");
    }

    public function removeAdmin(User $user)
    {
        $user->update(['role' => 'user']);
        $user->permissions()->delete();
        return back()->with('success', "{$user->name} admin huquqi olindi.");
    }
}
