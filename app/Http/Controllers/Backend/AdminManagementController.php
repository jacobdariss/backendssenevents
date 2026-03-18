<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AdminManagementController extends Controller
{
    // ─────────────────────────────────────────────
    //  PAGE PRINCIPALE
    // ─────────────────────────────────────────────
    public function index()
    {
        $roles = Role::where('name', '!=', 'user')
            ->withCount(['users' => fn($q) => $q->where('name', '!=', 'user')])
            ->orderBy('is_fixed', 'desc')
            ->orderBy('title')
            ->get();

        $adminUsers = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('name', '!=', 'user'))
            ->orderBy('name')
            ->get();

        $availableRoles = Role::where('name', '!=', 'user')
            ->orderBy('title')
            ->get();

        return view('backend.admin-management.index', compact('roles', 'adminUsers', 'availableRoles'));
    }

    // ─────────────────────────────────────────────
    //  ADMIN USERS
    // ─────────────────────────────────────────────
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|exists:roles,name',
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'name'       => $request->name,
            'first_name' => explode(' ', $request->name)[0],
            'last_name'  => trim(substr($request->name, strpos($request->name, ' '))),
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'status'     => 1,
            'user_type'  => $request->role,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json([
            'status'  => true,
            'message' => __('messages.create_form', ['form' => __('messages.admin_user')]),
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first(),
                'badge' => '<span class="badge bg-primary">' . ucfirst($user->getRoleNames()->first()) . '</span>',
            ],
        ]);
    }

    public function updateAdminRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        if ($user->hasRole('admin') && auth()->id() !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.permission_denied'),
            ], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'status'  => true,
            'message' => __('messages.update_form', ['form' => __('messages.admin_user')]),
        ]);
    }

    public function destroyAdmin($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.cannot_delete_yourself'),
            ], 403);
        }

        if ($user->hasRole('admin')) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.permission_denied'),
            ], 403);
        }

        $user->syncRoles([]);
        $user->status = 0;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => __('messages.delete_form', ['form' => __('messages.admin_user')]),
        ]);
    }

    // ─────────────────────────────────────────────
    //  RÔLES
    // ─────────────────────────────────────────────
    public function storeRole(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191|unique:roles,title',
        ]);

        $name = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9_ ]/', '', $request->title)));

        $role = Role::create([
            'name'       => $name,
            'title'      => $request->title,
            'guard_name' => 'web',
            'is_fixed'   => 0,
        ]);

        // Import permissions from another role
        if ($request->import_role) {
            $sourceRole = Role::find($request->import_role);
            if ($sourceRole) {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                $role->permissions()->syncWithoutDetaching($sourceRole->permissions);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => __('messages.create_form_role', ['form' => __('page.lbl_role')]),
            'data'    => [
                'id'       => $role->id,
                'title'    => $role->title,
                'name'     => $role->name,
                'is_fixed' => $role->is_fixed,
            ],
        ]);
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_fixed) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.permission_denied'),
            ], 403);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'status'  => true,
            'message' => __('messages.delete_form_role', ['form' => __('page.lbl_role')]),
        ]);
    }
}
