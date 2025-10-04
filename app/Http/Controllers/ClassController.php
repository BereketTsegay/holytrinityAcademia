<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with(['department', 'teacher', 'subjects'])->get();
        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'teacher_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1'
        ]);

        $class = ClassModel::create($request->all());

        return response()->json($class, 201);
    }

    public function show(ClassModel $class)
    {
        $class->load(['department', 'teacher', 'subjects', 'attendances']);
        return response()->json($class);
    }

    public function update(Request $request, ClassModel $class)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
            'teacher_id' => 'sometimes|required|exists:users,id',
            'capacity' => 'sometimes|required|integer|min:1'
        ]);

        $class->update($request->all());

        return response()->json($class);
    }

    public function destroy(ClassModel $class)
    {
        $class->delete();
        return response()->json(null, 204);
    }
}
