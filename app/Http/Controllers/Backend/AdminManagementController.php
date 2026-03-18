<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Helpers\AuthHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
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
            ->orderBy('first_name')
            ->get();

        $availableRoles = Role::where('name', '!=', 'user')
            ->orderBy('title')
            ->get();

        $modules     = config('constant.MODULES');
        $permissions = Permission::get();

        return view('backend.admin-management.index', compact(
            'roles', 'adminUsers', 'availableRoles', 'modules', 'permissions'
        ));
    }

    // ─────────────────────────────────────────────
    //  ADMIN USERS
    // ─────────────────────────────────────────────
    public function storeAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|exists:roles,name',
            'password' => ['required', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $nameParts = explode(' ', $request->name, 2);
        $user = User::create([
            'first_name' => $nameParts[0],
            'last_name'  => $nameParts[1] ?? '',
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
                'name'  => $user->full_name,
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first(),
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191|unique:roles,title',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

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
