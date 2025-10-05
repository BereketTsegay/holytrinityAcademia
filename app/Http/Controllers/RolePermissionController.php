<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role->load('permissions')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::beginTransaction();

        try {
            if ($request->has('name')) {
                $role->update(['name' => $request->name]);
            }

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteRole(Role $role)
    {
        // Prevent deletion of basic roles
        $protectedRoles = ['admin', 'teacher', 'student'];
        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'message' => 'This role cannot be deleted'
            ], 422);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role that has users assigned'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    public function getPermissions()
    {
        $permissions = Permission::all()->groupBy('group');
        return response()->json($permissions);
    }

    public function syncPermissions(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|string',
            'permissions.*.group' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            // Get existing permissions
            $existingPermissions = Permission::all()->keyBy('name');

            foreach ($request->permissions as $permissionData) {
                if (!$existingPermissions->has($permissionData['name'])) {
                    Permission::create([
                        'name' => $permissionData['name'],
                        'group' => $permissionData['group'],
                        'guard_name' => 'web'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Permissions synced successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to sync permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getRolesWithCounts()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        return response()->json($roles);
    }
}