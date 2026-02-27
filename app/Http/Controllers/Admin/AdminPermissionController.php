<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPermissionController extends Controller
{
    // Granular permissions: page.action
    public const PAGES = [
        'subjects.view'     => '👁 Fanlar — ko\'rish',
        'subjects.create'   => '➕ Fanlar — qo\'shish',
        'subjects.edit'     => '✏️ Fanlar — tahrirlash',
        'subjects.delete'   => '🗑 Fanlar — o\'chirish',

        'quizzes.view'      => '👁 Testlar — ko\'rish',
        'quizzes.create'    => '➕ Testlar — qo\'shish',
        'quizzes.edit'      => '✏️ Testlar — tahrirlash',
        'quizzes.delete'    => '🗑 Testlar — o\'chirish',

        'questions.view'    => '👁 Savollar — ko\'rish',
        'questions.create'  => '➕ Savollar — qo\'shish',
        'questions.edit'    => '✏️ Savollar — tahrirlash',
        'questions.delete'  => '🗑 Savollar — o\'chirish',

        'stats.view'        => '👁 Statistika — ko\'rish',

        'broadcast.view'    => '👁 Broadcast — ko\'rish',
        'broadcast.send'    => '📤 Broadcast — yuborish',

        'users.view'        => '👁 Foydalanuvchilar — ko\'rish',
        'users.block'       => '🚫 Foydalanuvchilar — bloklash',
        'users.delete'      => '🗑 Foydalanuvchilar — o\'chirish',
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

        try {
            $adminId = (int) $request->admin_id;
            $permissions = $request->permissions ?? [];

            \Log::info('[Permissions] store called', [
                'admin_id'    => $adminId,
                'permissions' => $permissions,
            ]);

            // Delete all existing permissions for this admin (raw)
            \DB::table('admin_permissions')->where('admin_id', $adminId)->delete();

            // Insert new ones
            $validPages = array_keys(self::PAGES);
            foreach ($permissions as $page) {
                if (in_array($page, $validPages)) {
                    \DB::table('admin_permissions')->insert([
                        'admin_id'   => $adminId,
                        'page'       => $page,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $saved = \DB::table('admin_permissions')->where('admin_id', $adminId)->count();
            \Log::info('[Permissions] saved count', ['count' => $saved]);

            $admin = User::findOrFail($adminId);
            return redirect()->route('admin.permissions.index')
                ->with('success', "✅ {$admin->name} uchun {$saved} ta ruxsat saqlandi.");

        } catch (\Exception $e) {
            \Log::error('[Permissions] store error: ' . $e->getMessage());
            return redirect()->route('admin.permissions.index')
                ->with('error', "❌ Xatolik: " . $e->getMessage());
        }
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

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        // Give selected permissions
        foreach ($request->permissions ?? [] as $page) {
            if (array_key_exists($page, self::PAGES)) {
                AdminPermission::create(['admin_id' => $admin->id, 'page' => $page]);
            }
        }

        return back()->with('success', "{$admin->name} muvaffaqiyatli admin sifatida yaratildi.");
    }

    public function updateAdmin(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', "{$user->name} ma'lumotlari yangilandi.");
    }
}
