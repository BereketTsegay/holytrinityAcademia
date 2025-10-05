<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'teacherClasses', 'attendances'])
                    ->withCount(['teacherClasses', 'attendances']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,teacher,student',
            'status' => 'required|in:active,inactive,suspended',
            'send_invitation' => 'boolean',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $tempPassword = Str::random(12);
            
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($tempPassword),
                'status' => $request->status,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'qualification' => $request->qualification,
                'specialization' => $request->specialization,
            ]);

            // Assign role
            $user->assignRole($request->role);

            // Generate IDs based on role
            if ($request->role === 'student') {
                $user->update([
                    'student_id' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT)
                ]);
            } else {
                $user->update([
                    'employee_id' => 'EMP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'joining_date' => now(),
                ]);
            }

            // Send invitation email if requested
            if ($request->boolean('send_invitation')) {
                try {
                    Mail::to($user->email)->send(new UserInvitation($user, $tempPassword));
                } catch (\Exception $e) {
                    \Log::error('Invitation email failed: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'User created successfully' . ($request->send_invitation ? ' and invitation sent.' : '.'),
                'user' => $user->load('roles')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user)
    {
        $user->load(['roles', 'teacherClasses.department', 'subjects', 'attendances.class']);
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'roles' => 'sometimes|array',
            'roles.*' => 'in:admin,teacher,student',
            'status' => 'sometimes|required|in:active,inactive,suspended',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_information' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);

        $user->update($request->except('roles'));

        // Update roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,suspend,delete'
        ]);

        DB::beginTransaction();

        try {
            $users = User::whereIn('id', $request->user_ids)
                        ->where('id', '!=', auth()->id()) // Prevent self-modification
                        ->get();

            foreach ($users as $user) {
                switch ($request->action) {
                    case 'activate':
                        $user->update(['status' => 'active']);
                        break;
                    case 'deactivate':
                        $user->update(['status' => 'inactive']);
                        break;
                    case 'suspend':
                        $user->update(['status' => 'suspended']);
                        break;
                    case 'delete':
                        $user->delete();
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk action completed successfully',
                'affected_users' => $users->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bulk action failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendInvitation(User $user)
    {
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'User has already verified their email'
            ], 422);
        }

        $tempPassword = Str::random(12);
        $user->update([
            'password' => Hash::make($tempPassword)
        ]);

        try {
            Mail::to($user->email)->send(new UserInvitation($user, $tempPassword));
            
            return response()->json([
                'message' => 'Invitation sent successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Invitation email failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send invitation email'
            ], 500);
        }
    }

    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function getPermissions()
    {
        $permissions = Permission::all()->groupBy('group');
        return response()->json($permissions);
    }

    public function updateUserPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $user->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'User permissions updated successfully',
            'user' => $user->load(['roles', 'permissions'])
        ]);
    }

    public function getUserStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::role('student')->count(),
            'total_teachers' => User::role('teacher')->count(),
            'total_admins' => User::role('admin')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'recently_joined' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json($stats);
    }
}