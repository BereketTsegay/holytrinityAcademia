<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Password reset routes
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/validate-reset-token', [AuthController::class, 'validateResetToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Departments
    Route::apiResource('departments', DepartmentController::class);

    // Classes
    Route::apiResource('classes', ClassController::class);

    // Subjects
    Route::apiResource('subjects', SubjectController::class);

    // Attendance
    Route::apiResource('attendance', AttendanceController::class);
    Route::get('/attendance/class/{classId}', [AttendanceController::class, 'getClassAttendance']);
    Route::post('/attendance/bulk', [AttendanceController::class, 'bulkStore']);

    // Library
    Route::apiResource('books', BookController::class);
    Route::post('/books/{book}/borrow', [BookController::class, 'borrow']);
    Route::post('/books/{book}/return', [BookController::class, 'returnBook']);

    // Calendar
    Route::apiResource('events', EventController::class);

    // Reports
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport']);
    Route::get('/reports/library', [ReportController::class, 'libraryReport']);
    Route::get('/reports/academic', [ReportController::class, 'academicReport']);
});
