<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'class']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $attendance = $query->orderBy('date', 'desc')
                           ->paginate($request->per_page ?? 20);

        return response()->json($attendance);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string'
        ]);

        // Check if attendance already exists for this student, class, and date
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Attendance already recorded for this student on this date'
            ], 422);
        }

        $attendance = Attendance::create($request->all());

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'attendance' => $attendance->load(['student', 'class'])
        ], 201);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $createdAttendances = [];

            foreach ($request->attendances as $attendanceData) {
                // Delete existing attendance for this student, class, and date
                Attendance::where('student_id', $attendanceData['student_id'])
                    ->where('class_id', $request->class_id)
                    ->where('date', $request->date)
                    ->delete();

                $attendance = Attendance::create([
                    'student_id' => $attendanceData['student_id'],
                    'class_id' => $request->class_id,
                    'date' => $request->date,
                    'status' => $attendanceData['status'],
                    'notes' => $attendanceData['notes'] ?? null
                ]);

                $createdAttendances[] = $attendance->load('student');
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk attendance recorded successfully',
                'attendances' => $createdAttendances
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['student', 'class']);
        return response()->json($attendance);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'sometimes|required|in:present,absent,late,excused',
            'notes' => 'nullable|string'
        ]);

        $attendance->update($request->all());

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance->load(['student', 'class'])
        ]);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    public function getClassAttendance($classId, Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $class = ClassModel::with(['students'])->findOrFail($classId);

        // Get existing attendance for the date
        $existingAttendance = Attendance::with('student')
            ->where('class_id', $classId)
            ->where('date', $request->date)
            ->get()
            ->keyBy('student_id');

        // Prepare response with all students and their attendance status
        $studentsWithAttendance = $class->students->map(function ($student) use ($existingAttendance) {
            $attendance = $existingAttendance->get($student->id);

            return [
                'student' => $student,
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes
                ] : null
            ];
        });

        return response()->json([
            'class' => $class,
            'date' => $request->date,
            'students' => $studentsWithAttendance
        ]);
    }

    public function studentReport($studentId, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $attendances = Attendance::with('class')
            ->where('student_id', $studentId)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $summary = $attendances->groupBy('status')->map->count();
        $total = $attendances->count();
        $attendanceRate = $total > 0 ? ($summary['present'] ?? 0) / $total * 100 : 0;

        return response()->json([
            'attendances' => $attendances,
            'summary' => $summary,
            'total' => $total,
            'attendance_rate' => round($attendanceRate, 2)
        ]);
    }
}
