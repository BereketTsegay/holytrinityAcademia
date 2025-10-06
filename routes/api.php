<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
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


  
    // Department routes
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::get('/departments/{department}', [DepartmentController::class, 'show']);
    Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
    Route::post('/departments/bulk-action', [DepartmentController::class, 'bulkAction']);
    Route::get('/departments/{department}/teachers', [DepartmentController::class, 'getTeachers']);
    Route::get('/departments/stats', [DepartmentController::class, 'getStats']);
    Route::get('/departments-list', [DepartmentController::class, 'getAll']);

// Book report routes
Route::get('/books/reports', [BookController::class, 'reports']);

// Department report routes
Route::get('/reports/departments', [ReportController::class, 'departmentReport']);

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

    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction']);
    Route::post('/users/{user}/send-invitation', [UserController::class, 'sendInvitation']);
    Route::get('/user-stats', [UserController::class, 'getUserStats']);
    
    // Role and permission routes
    Route::get('/roles', [RolePermissionController::class, 'getRoles']);
    Route::post('/roles', [RolePermissionController::class, 'createRole']);
    Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole']);
    Route::delete('/roles/{role}', [RolePermissionController::class, 'deleteRole']);
    Route::get('/permissions', [RolePermissionController::class, 'getPermissions']);
    Route::post('/permissions/sync', [RolePermissionController::class, 'syncPermissions']);

    // Calendar routes
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::get('/calendar/{event}', [CalendarController::class, 'show']);
    Route::put('/calendar/{event}', [CalendarController::class, 'update']);
    Route::delete('/calendar/{event}', [CalendarController::class, 'destroy']);
    Route::post('/calendar/bulk-delete', [CalendarController::class, 'bulkDelete']);
    Route::get('/calendar/upcoming', [CalendarController::class, 'upcomingEvents']);
    Route::get('/calendar/statistics', [CalendarController::class, 'eventStatistics']);

});
