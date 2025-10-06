<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BookBorrowing;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportController extends Controller
{
    public function attendanceReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id',
            'student_id' => 'nullable|exists:users,id',
            'type' => 'required|in:summary,detailed'
        ]);

        $query = Attendance::with(['student', 'class'])
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->type === 'summary') {
            $report = $this->generateAttendanceSummary($query, $request);
        } else {
            $report = $this->generateAttendanceDetailed($query, $request);
        }

        return response()->json($report);
    }

    public function libraryReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:borrowings,books,overdue'
        ]);

        switch ($request->type) {
            case 'borrowings':
                $report = $this->generateBorrowingsReport($request);
                break;
            case 'books':
                $report = $this->generateBooksReport($request);
                break;
            case 'overdue':
                $report = $this->generateOverdueReport($request);
                break;
        }

        return response()->json($report);
    }

    public function academicReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'type' => 'required|in:class,student'
        ]);

        if ($request->type === 'class') {
            $report = $this->generateClassAcademicReport($request->class_id);
        } else {
            $report = $this->generateStudentAcademicReport($request);
        }

        return response()->json($report);
    }

    private function generateAttendanceSummary($query, $request)
    {
        $summary = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as excused
        ')->first();

        $attendanceRate = $summary->total > 0 ? ($summary->present / $summary->total) * 100 : 0;

        return [
            'summary' => $summary,
            'attendance_rate' => round($attendanceRate, 2),
            'period' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
    }

    private function generateAttendanceDetailed($query, $request)
    {
        $attendances = $query->orderBy('date')->get();

        $grouped = $attendances->groupBy('student_id')->map(function ($studentAttendances) {
            $student = $studentAttendances->first()->student;

            return [
                'student' => $student,
                'attendances' => $studentAttendances,
                'summary' => [
                    'total' => $studentAttendances->count(),
                    'present' => $studentAttendances->where('status', 'present')->count(),
                    'absent' => $studentAttendances->where('status', 'absent')->count(),
                    'late' => $studentAttendances->where('status', 'late')->count(),
                    'excused' => $studentAttendances->where('status', 'excused')->count(),
                ]
            ];
        })->values();

        return [
            'attendances' => $grouped,
            'period' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
    }

    private function generateBorrowingsReport($request)
    {
        $borrowings = BookBorrowing::with(['book', 'student'])
            ->whereBetween('borrowed_date', [$request->start_date, $request->end_date])
            ->get();

        $summary = [
            'total_borrowings' => $borrowings->count(),
            'active_borrowings' => $borrowings->where('status', 'borrowed')->count(),
            'returned_books' => $borrowings->where('status', 'returned')->count(),
            'overdue_books' => $borrowings->where('status', 'overdue')->count(),
        ];

        $popularBooks = $borrowings->groupBy('book_id')
            ->map(function ($bookBorrowings) {
                return [
                    'book' => $bookBorrowings->first()->book,
                    'borrow_count' => $bookBorrowings->count()
                ];
            })
            ->sortByDesc('borrow_count')
            ->take(10)
            ->values();

        return [
            'summary' => $summary,
            'popular_books' => $popularBooks,
            'borrowings' => $borrowings,
            'period' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ];
    }

    private function generateBooksReport($request)
    {
        $books = \App\Models\Book::withCount(['borrowings', 'currentBorrowings'])
            ->get();

        $categories = $books->groupBy('category')
            ->map(function ($categoryBooks) {
                return [
                    'total_books' => $categoryBooks->count(),
                    'total_copies' => $categoryBooks->sum('quantity'),
                    'available_copies' => $categoryBooks->sum('available')
                ];
            });

        return [
            'total_books' => $books->count(),
            'total_copies' => $books->sum('quantity'),
            'available_copies' => $books->sum('available'),
            'borrowed_copies' => $books->sum('quantity') - $books->sum('available'),
            'categories' => $categories
        ];
    }

    private function generateOverdueReport($request)
    {
        $overdueBooks = BookBorrowing::with(['book', 'student'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->get();

        return [
            'overdue_count' => $overdueBooks->count(),
            'overdue_books' => $overdueBooks,
            'generated_at' => now()
        ];
    }

    private function generateClassAcademicReport($classId)
    {
        $class = ClassModel::with(['students', 'teacher', 'subjects'])->find($classId);

        $attendanceSummary = Attendance::where('class_id', $classId)
            ->selectRaw('
                student_id,
                COUNT(*) as total_classes,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count
            ')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return [
            'class' => $class,
            'attendance_summary' => $attendanceSummary,
            'total_students' => $class->students->count(),
            'report_date' => now()
        ];
    }

    private function generateStudentAcademicReport($request)
    {
        $student = User::with(['attendances.class', 'borrowedBooks.book'])->find($request->student_id);

        $attendanceSummary = $student->attendances()
            ->selectRaw('
                class_id,
                COUNT(*) as total_classes,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count
            ')
            ->groupBy('class_id')
            ->get();

        return [
            'student' => $student,
            'attendance_summary' => $attendanceSummary,
            'current_borrowings' => $student->borrowedBooks()->where('status', 'borrowed')->count(),
            'report_date' => now()
        ];
    }

    public function exportReport(Request $request, $type)
    {
        $request->validate([
            'format' => 'required|in:pdf,csv'
        ]);

        $reportData = [];

        switch ($type) {
            case 'attendance':
                $reportData = $this->attendanceReport($request);
                break;
            case 'library':
                $reportData = $this->libraryReport($request);
                break;
            case 'academic':
                $reportData = $this->academicReport($request);
                break;
        }

        if ($request->format === 'pdf') {
            return $this->generatePdfReport($reportData, $type);
        } else {
            return $this->generateCsvReport($reportData, $type);
        }
    }

    private function generatePdfReport($data, $type)
    {
        $pdf = PDF::loadView("reports.{$type}", $data);
        return $pdf->download("{$type}_report_" . now()->format('Y_m_d') . '.pdf');
    }

    private function generateCsvReport($data, $type)
    {
        // CSV generation logic would go here
        return response()->json(['message' => 'CSV export not implemented yet']);
    }

    public function departmentReport(Request $request)
{
    $request->validate([
        'department_id' => 'required|exists:departments,id',
        'report_type' => 'required|in:overview,classes,teachers,students'
    ]);

    $department = Department::with(['classes', 'subjects'])->find($request->department_id);

    switch ($request->report_type) {
        case 'overview':
            return $this->departmentOverviewReport($department);
        case 'classes':
            return $this->departmentClassesReport($department);
        case 'teachers':
            return $this->departmentTeachersReport($department);
        case 'students':
            return $this->departmentStudentsReport($department, $request);
    }
}

private function departmentOverviewReport($department)
{
    $stats = [
        'total_classes' => $department->classes->count(),
        'total_subjects' => $department->subjects->count(),
        'total_students' => $department->classes->sum(function ($class) {
            return $class->students->count();
        }),
        'total_teachers' => $department->classes->unique('teacher_id')->count(),
        'average_class_size' => $department->classes->avg(function ($class) {
            return $class->students->count();
        })
    ];

    return response()->json([
        'department' => $department,
        'stats' => $stats,
        'report_date' => now()
    ]);
}

private function departmentClassesReport($department)
{
    $classes = $department->classes()
        ->with(['teacher', 'students'])
        ->withCount('students')
        ->get()
        ->map(function ($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'teacher' => $class->teacher,
                'student_count' => $class->students_count,
                'capacity' => $class->capacity,
                'utilization_rate' => $class->capacity > 0 ? ($class->students_count / $class->capacity) * 100 : 0
            ];
        });

    return response()->json([
        'department' => $department->only(['id', 'name']),
        'classes' => $classes,
        'report_date' => now()
    ]);
}

private function departmentTeachersReport($department)
{
    $teachers = User::role('teacher')
        ->whereHas('teacherClasses', function ($query) use ($department) {
            $query->where('department_id', $department->id);
        })
        ->withCount(['teacherClasses' => function ($query) use ($department) {
            $query->where('department_id', $department->id);
        }])
        ->with(['subjects'])
        ->get();

    return response()->json([
        'department' => $department->only(['id', 'name']),
        'teachers' => $teachers,
        'report_date' => now()
    ]);
}

private function departmentStudentsReport($department, $request)
{
    $students = User::role('student')
        ->whereHas('attendances.class', function ($query) use ($department) {
            $query->where('department_id', $department->id);
        })
        ->with(['attendances' => function ($query) use ($department, $request) {
            $query->whereHas('class', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });
        }])
        ->get()
        ->map(function ($student) {
            $totalClasses = $student->attendances->count();
            $presentClasses = $student->attendances->where('status', 'present')->count();
            
            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'email' => $student->email,
                'student_id' => $student->student_id,
                'total_classes' => $totalClasses,
                'present_classes' => $presentClasses,
                'attendance_rate' => $totalClasses > 0 ? ($presentClasses / $totalClasses) * 100 : 0
            ];
        });

    return response()->json([
        'department' => $department->only(['id', 'name']),
        'students' => $students,
        'report_date' => now()
    ]);
}
}
