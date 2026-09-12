<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends BaseAdminController
{
    public function users(Request $request)
    {
        $this->requireAdmin();
        $query = User::withCount(['posts', 'comments']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($sub) => $sub->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")->orWhere('username', 'like', "%$q%"));
        }
        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('status')) {
            $request->status === 'banned' ? $query->where('is_banned', true) : $query->where('is_banned', false);
        }
        $perPage = (int) $request->input('per_page', 20);
        return view('admin.users', ['users' => $query->latest()->paginate($perPage)->withQueryString(), 'roles' => Role::orderBy('sort_order')->get()]);
    }

    public function createUser()
    {
        $this->requireAdmin();
        return view('admin.user-create', ['roles' => Role::orderBy('sort_order')->get()]);
    }

    public function storeUser(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:reader,reporter,editor,admin',
        ]);
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => $data['role'],
            'uuid'     => (string) Str::uuid(),
        ]);
        return redirect('/admin/users/' . $user->id)->with('success', 'User created.');
    }

    public function showUser(int $id)
    {
        $this->requireAdmin();
        $user = User::withCount(['posts', 'comments'])->findOrFail($id);
        return view('admin.user-details', compact('user'));
    }

    public function editUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        return view('admin.user-edit', ['user' => $user, 'roles' => Role::orderBy('sort_order')->get(), 'platforms' => User::socialPlatforms()]);
    }

    public function updateUser(Request $request, int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'first_name' => 'nullable|string|max:80',
            'last_name'  => 'nullable|string|max:80',
            'username'   => 'required|string|max:60|unique:users,username,' . $id,
            'email'      => 'required|email|unique:users,email,' . $id,
            'role'       => 'required|in:reader,reporter,editor,admin',
            'bio'        => 'nullable|string|max:500',
            'balance'    => 'nullable|numeric|min:0',
            'website'    => 'nullable|url|max:255',
        ]);
        $data['is_banned']     = $request->input('status') === 'banned';
        $data['reward_system'] = $request->boolean('reward_system');
        if ($request->boolean('verify_email') && !$user->email_verified_at) {
            $data['email_verified_at'] = now();
        }
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = bcrypt($request->password);
        }
        $social = array_filter($request->input('social_links', []), fn($v) => !empty(trim((string) $v)));
        $data['social_links'] = $social ?: null;
        $user->update($data);
        return redirect('/admin/users/' . $id)->with('success', 'User updated.');
    }

    public function deleteUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot delete yourself.');
        if ($user->role === 'admin')    return back()->with('error', 'Cannot delete an admin account.');
        $user->delete();
        return redirect('/admin/users')->with('success', 'User deleted.');
    }

    public function verifyEmail(int $id)
    {
        $this->requireAdmin();
        User::findOrFail($id)->update(['email_verified_at' => now()]);
        return back()->with('success', 'Email marked as verified.');
    }

    public function toggleRewardSystem(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $user->update(['reward_system' => !$user->reward_system]);
        return back()->with('success', 'Reward system ' . ($user->reward_system ? 'enabled' : 'disabled') . '.');
    }

    public function userPermissions(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        return view('admin.user-permissions', ['user' => $user, 'allPerms' => Role::allPermissions(), 'roles' => Role::orderBy('sort_order')->get()]);
    }

    public function updateUserPermissions(Request $request, int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $user->update([
            'role'              => $request->input('role', $user->role),
            'extra_permissions' => $request->input('permissions', []),
        ]);
        return back()->with('success', 'Permissions updated.');
    }

    public function impersonate(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot impersonate yourself.');
        session(['impersonating_admin_id' => auth()->id()]);
        auth()->loginUsingId($id);
        return redirect('/')->with('info', 'You are now logged in as ' . $user->name . '. <a href="/admin/users/stop-impersonating" class="underline font-semibold">Return to Admin</a>');
    }

    public function stopImpersonating()
    {
        $adminId = session('impersonating_admin_id');
        if (!$adminId) return redirect('/admin');
        session()->forget('impersonating_admin_id');
        auth()->loginUsingId($adminId);
        return redirect('/admin/users')->with('success', 'Returned to your admin account.');
    }

    public function updateUserRole(Request $request, int $id)
    {
        $this->requireAdmin();
        User::findOrFail($id)->update($request->validate(['role' => 'required|in:reader,reporter,editor,admin']));
        return back()->with('success', 'Role updated.');
    }

    public function banUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot ban yourself.');
        $user->update(['is_banned' => !($user->is_banned ?? false)]);
        return back()->with('success', $user->is_banned ? 'User banned.' : 'User unbanned.');
    }

    // ── Roles ─────────────────────────────────────────────────

    public function roles()
    {
        $this->requireAdmin();
        $this->seedDefaultRoles();
        $roles = Role::orderBy('sort_order')->get();
        return view('admin.roles', ['roles' => $roles, 'allPerms' => Role::allPermissions(), 'badgeOptions' => Role::badgeOptions()]);
    }

    public function storeRole(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'name_ne'     => 'nullable|string|max:100',
            'slug'        => 'nullable|string|max:80',
            'badge_label' => 'nullable|string|max:80',
            'badge_color' => 'nullable|string|max:30',
            'badge_icon'  => 'nullable|string|max:30',
            'ai_credits'  => 'nullable|integer|min:0|max:100000',
            'permissions' => 'nullable|array',
        ]);
        $data['slug']        = Str::slug($data['slug'] ?? $data['name']);
        $data['sort_order']  = Role::max('sort_order') + 1;
        $data['permissions'] = $request->input('permissions', []);
        Role::create($data);
        return back()->with('success', 'Role created.');
    }

    public function updateRole(Request $request, int $id)
    {
        $this->requireAdmin();
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'name_ne'     => 'nullable|string|max:100',
            'badge_label' => 'nullable|string|max:80',
            'badge_color' => 'nullable|string|max:30',
            'badge_icon'  => 'nullable|string|max:30',
            'ai_credits'  => 'nullable|integer|min:0|max:100000',
            'permissions' => 'nullable|array',
        ]);
        $data['permissions'] = $request->input('permissions', []);
        $role->update($data);
        return back()->with('success', 'Role updated.');
    }

    public function deleteRole(int $id)
    {
        $this->requireAdmin();
        $role = Role::findOrFail($id);
        if ($role->is_system) return back()->with('error', 'Cannot delete a system role.');
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

    private function seedDefaultRoles(): void
    {
        if (Role::count() > 0) return;
        $defaults = [
            ['name' => 'Super Admin', 'slug' => 'admin',    'badge_label' => 'Super Admin', 'badge_color' => 'red',    'badge_icon' => 'shield', 'permissions' => array_keys(Role::allPermissions()), 'is_default' => true, 'is_system' => true, 'ai_credits' => 99999, 'sort_order' => 0],
            ['name' => 'Editor',      'slug' => 'editor',   'badge_label' => 'Editor',      'badge_color' => 'purple', 'badge_icon' => 'pencil', 'permissions' => ['add_post', 'edit_own_post', 'ai_writer', 'categories', 'tags', 'comments', 'media'], 'is_default' => true, 'is_system' => true, 'ai_credits' => 500,   'sort_order' => 1],
            ['name' => 'Reporter',    'slug' => 'reporter', 'badge_label' => 'Author',      'badge_color' => 'green',  'badge_icon' => 'user',   'permissions' => ['add_post', 'edit_own_post', 'ai_writer', 'tags'], 'is_default' => true, 'is_system' => true, 'ai_credits' => 100,   'sort_order' => 2],
            ['name' => 'Reader',      'slug' => 'reader',   'badge_label' => 'Member',      'badge_color' => 'gray',   'badge_icon' => 'user',   'permissions' => ['comments'], 'is_default' => true, 'is_system' => true, 'ai_credits' => 0,     'sort_order' => 3],
        ];
        foreach ($defaults as $d) {
            Role::create($d);
        }
    }
}
