<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\{DashboardController, UserController, GroupController, ModuleController, SessionController, AbsenceController, JustificationController, StatisticController, SettingController};
use App\Http\Controllers\Teacher\{DashboardController as TeacherDashboard, ScheduleController, AttendanceController, ModuleController as TeacherModuleController, ReportController};
use App\Http\Controllers\Student\{DashboardController as StudentDashboard, AbsenceController as StudentAbsenceController, JustificationController as StudentJustificationController, ScheduleController as StudentScheduleController, ProfileController, NotificationController};
use Illuminate\Support\Facades\Auth;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| All routes are loaded by the RouteServiceProvider.
|
*/

// Public routes
Route::get('/', function (){
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\AuthController::class, 'reset'])->name('password.update');
});

// Logout route (accessible to all authenticated users)
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
// Admin routes (all require admin role)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Group management
    Route::resource('groups', GroupController::class);
    Route::get('groups/{group}/students', [GroupController::class, 'students'])->name('groups.students');
    Route::post('groups/{group}/add-student', [GroupController::class, 'addStudent'])->name('groups.add-student');
    Route::delete('groups/{group}/remove-student/{student}', [GroupController::class, 'removeStudent'])->name('groups.remove-student');

    // Module management
    Route::resource('modules', ModuleController::class);
    Route::post('modules/{module}/assign-teacher', [ModuleController::class, 'assignTeacher'])->name('modules.assign-teacher');
    Route::post('modules/{module}/assign-group', [ModuleController::class, 'assignGroup'])->name('modules.assign-group');
    Route::delete('modules/{module}/remove-teacher/{teacher}', [ModuleController::class, 'removeTeacher'])->name('modules.remove-teacher');
    Route::delete('modules/{module}/remove-group/{group}', [ModuleController::class, 'removeGroup'])->name('modules.remove-group');

    // Session management
    Route::resource('sessions', SessionController::class);
    Route::post('sessions/{session}/cancel', [SessionController::class, 'cancel'])->name('sessions.cancel');

    // Absence management
    Route::get('absences', [AbsenceController::class, 'index'])->name('absences.index');
    Route::post('absences/export', [AbsenceController::class, 'export'])->name('absences.export');

    // Justification management
    Route::get('justifications', [JustificationController::class, 'index'])->name('justifications.index');
    Route::get('justifications/{justification}', [JustificationController::class, 'show'])->name('justifications.show');
    Route::post('justifications/{justification}/validate', [JustificationController::class, 'validateJustification'])->name('justifications.validate');

    // Statistics
    Route::get('statistics', [StatisticController::class, 'index'])->name('statistics.index');
    Route::post('statistics/export', [StatisticController::class, 'export'])->name('statistics.export');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // Import/Export
    Route::get('import', [UserController::class, 'showImportForm'])->name('import.index');
    Route::post('import/students', [UserController::class, 'importStudents'])->name('import.students');
    Route::post('import/export-students', [UserController::class, 'exportStudents'])->name('import.export-students');
});

// Teacher routes (require teacher role)
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');
    
    // Schedule
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/{session}', [ScheduleController::class, 'show'])->name('schedule.show');
    Route::post('/schedule/{session}/cancel', [ScheduleController::class, 'cancel'])->name('schedule.cancel');
    
    // Attendance
    Route::get('/attendance/{session}/take', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{session}', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/{session}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{session}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/{session}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    
    // Modules
    Route::get('/modules', [TeacherModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [TeacherModuleController::class, 'show'])->name('modules.show');
    Route::get('/modules/{module}/statistics', [TeacherModuleController::class, 'statistics'])->name('modules.statistics');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{module}/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
    Route::post('/reports/export-attendance', [ReportController::class, 'exportAttendance'])->name('reports.export-attendance');
});

// Student routes (require student role)
// In your routes/web.php, add these to the student group:

// Profile routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
Route::get('/profile/academic', [ProfileController::class, 'academicSummary'])->name('profile.academic');
Route::get('/profile/documents', [ProfileController::class, 'myDocuments'])->name('profile.documents');
Route::get('/profile/certificate', [ProfileController::class, 'downloadAttendanceCertificate'])->name('profile.certificate');
Route::get('/profile/export-data', [ProfileController::class, 'exportMyData'])->name('profile.export-data');
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    
    // Absences
    Route::get('/absences', [StudentAbsenceController::class, 'index'])->name('absences.index');
    Route::get('/absences/statistics', [StudentAbsenceController::class, 'statistics'])->name('absences.statistics');
    Route::get('/absences/report', [StudentAbsenceController::class, 'report'])->name('absences.report');
    
    // Justifications
    Route::get('/justifications', [StudentJustificationController::class, 'index'])->name('justifications.index');
    Route::get('/justifications/create/{absence}', [StudentJustificationController::class, 'create'])->name('justifications.create');
    Route::post('/justifications', [StudentJustificationController::class, 'store'])->name('justifications.store');
    Route::get('/justifications/{justification}', [StudentJustificationController::class, 'show'])->name('justifications.show');
    
    // Schedule
    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/missed-sessions', [StudentScheduleController::class, 'missedSessions'])->name('schedule.missed');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('/notifications/update-preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.update-preferences');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Common routes (accessible to all authenticated users)
Route::middleware('auth')->group(function () {
    // Profile update (common for all roles)
    Route::put('/profile/update', [App\Http\Controllers\Auth\AuthController::class, 'updateProfile'])->name('profile.update');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
