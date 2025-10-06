<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['classes', 'subjects', 'teachers']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('head_of_department', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort_field', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $departments = $query->paginate($request->get('per_page', 15));

        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'head_of_department' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();

        try {
            $department = Department::create($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Department created successfully',
                'department' => $department
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Department $department)
    {
        $department->load(['classes.teacher', 'subjects', 'classes.students']);
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'head_of_department' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $department->update($request->all());

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department
        ]);
    }

    public function destroy(Department $department)
    {
        // Check if department has classes
        if ($department->classes()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department that has classes assigned'
            ], 422);
        }

        // Check if department has subjects
        if ($department->subjects()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department that has subjects assigned'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
            'action' => 'required|in:delete'
        ]);

        DB::beginTransaction();

        try {
            $departments = Department::whereIn('id', $request->department_ids)
                                ->withCount(['classes', 'subjects'])
                                ->get();

            $deletedCount = 0;
            $errors = [];

            foreach ($departments as $department) {
                if ($department->classes_count > 0 || $department->subjects_count > 0) {
                    $errors[] = "Department '{$department->name}' cannot be deleted because it has classes or subjects assigned.";
                    continue;
                }

                $department->delete();
                $deletedCount++;
            }

            DB::commit();

            $response = [
                'message' => "Successfully deleted {$deletedCount} departments.",
                'deleted_count' => $deletedCount
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bulk action failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTeachers(Department $department)
    {
        $teachers = User::role('teacher')
                       ->whereHas('teacherClasses', function ($query) use ($department) {
                           $query->where('department_id', $department->id);
                       })
                       ->with(['teacherClasses'])
                       ->get();

        return response()->json($teachers);
    }

    public function getStats()
    {
        $stats = [
            'total_departments' => Department::count(),
            'total_classes' => DB::table('classes')->count(),
            'total_subjects' => DB::table('subjects')->count(),
            'total_teachers' => User::role('teacher')->count(),
            'departments_with_most_classes' => Department::withCount('classes')
                                                        ->orderBy('classes_count', 'desc')
                                                        ->limit(5)
                                                        ->get(),
            'departments_with_most_teachers' => Department::withCount('teachers')
                                                         ->orderBy('teachers_count', 'desc')
                                                         ->limit(5)
                                                         ->get(),
        ];

        return response()->json($stats);
    }

    public function getAll()
    {
        $departments = Department::select('id', 'name')->get();
        return response()->json($departments);
    }
}