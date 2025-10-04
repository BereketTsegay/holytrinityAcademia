<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\BookBorrowing;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $user = auth()->user();

        $stats = [
            'total_students' => User::role('student')->active()->count(),
            'total_teachers' => User::role('teacher')->active()->count(),
            'total_classes' => ClassModel::count(),
            'total_books' => Book::sum('quantity'),
            'available_books' => Book::sum('available'),
            'today_attendance' => Attendance::whereDate('date', today())
                                        ->where('status', 'present')
                                        ->count(),
        ];

        // Role-specific stats
        if ($user->isStudent()) {
            $studentStats = $this->getStudentStats($user);
            $stats = array_merge($stats, $studentStats);
        } elseif ($user->isTeacher()) {
            $teacherStats = $this->getTeacherStats($user);
            $stats = array_merge($stats, $teacherStats);
        }

        return response()->json($stats);
    }

    public function recentActivity()
    {
        $user = auth()->user();
        $activities = [];

        // Recent attendance
        if ($user->isStudent()) {
            $activities['attendance'] = Attendance::with('class')
                ->where('student_id', $user->id)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
        }

        // Recent book borrowings
        $activities['borrowings'] = BookBorrowing::with('book')
            ->where('student_id', $user->id)
            ->where('status', 'borrowed')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Upcoming events
        $activities['events'] = Event::where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json($activities);
    }

    public function chartData()
    {
        $user = auth()->user();

        // Attendance chart data (last 30 days)
        $attendanceData = Attendance::where('student_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('date, COUNT(*) as total, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Book categories distribution
        $bookCategories = Book::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        return response()->json([
            'attendance' => $attendanceData,
            'book_categories' => $bookCategories
        ]);
    }

    private function getStudentStats($user)
    {
        return [
            'my_attendance' => Attendance::where('student_id', $user->id)
                                ->where('status', 'present')
                                ->count(),
            'borrowed_books' => BookBorrowing::where('student_id', $user->id)
                                ->where('status', 'borrowed')
                                ->count(),
            'total_classes' => $user->attendances()->distinct('class_id')->count(),
        ];
    }

    private function getTeacherStats($user)
    {
        return [
            'my_classes' => $user->teacherClasses()->count(),
            'my_students' => User::role('student')
                            ->whereHas('attendances', function($q) use ($user) {
                                $q->whereIn('class_id', $user->teacherClasses()->pluck('id'));
                            })
                            ->count(),
            'pending_attendance' => Attendance::whereIn('class_id', $user->teacherClasses()->pluck('id'))
                                    ->whereDate('date', today())
                                    ->count(),
        ];
    }
}
