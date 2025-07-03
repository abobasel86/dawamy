<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\Manager\ApprovalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BalanceController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Manager\TeamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\Admin\WorkShiftController;
// --- START: إضافة المتحكمات الجديدة ---
use App\Http\Controllers\Admin\OfficialHolidayController;
use App\Http\Controllers\Admin\OvertimeApprovalController;
use App\Http\Controllers\Finance\OvertimeReportController;
// --- END: إضافة المتحكمات الجديدة ---
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\EnsureJustificationIsProvided;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\UserOvertimeRequestController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// WebAuthn Routes
Route::withoutMiddleware(VerifyCsrfToken::class)->group(function () {
    WebAuthnRoutes::register();
});

// Employee Routes
Route::middleware(['auth', EnsureJustificationIsProvided::class])->group(function () {
    Route::get('/dashboard', [AttendanceController::class, 'index'])->name('dashboard');
    Route::post('/punch-in', [AttendanceController::class, 'punchIn'])->name('attendance.punchin');
    Route::post('/punch-out', [AttendanceController::class, 'punchOut'])->name('attendance.punchout');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/overtime-approvals', [OvertimeRequestController::class, 'index'])->name('overtime.approvals.index');
    Route::post('/overtime-approvals/{overtimeRequest}', [OvertimeRequestController::class, 'processApproval'])->name('overtime.approvals.process');
    Route::get('/my-overtime-requests', [UserOvertimeRequestController::class, 'index'])->name('overtime.my-requests');


    // Notifications & Push Subscriptions
    Route::get('/notifications/{notification}', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications-summary', [NotificationController::class, 'summary'])->name('notifications.summary');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push_subscriptions.store');
    Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy'])->name('push_subscriptions.destroy');

    // Leave Requests
    Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
});

Route::middleware(['auth'])->group(function () {
    // --- START: مسارات إدخال السبب (جديدة) ---
    Route::get('/justification/create', [AttendanceController::class, 'createJustification'])->name('justification.create');
    Route::post('/justification/store', [AttendanceController::class, 'storeJustification'])->name('justification.store');
    // --- END: مسارات إدخال السبب (جديدة) ---
});

// Manager & Senior Roles Routes for Approvals
Route::middleware(['auth', 'role:manager|admin|secretary_general|assistant_secretary_general|HR'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{approval}', [ApprovalController::class, 'update'])->name('approvals.update');
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
});

// Admin Only Routes
Route::middleware(['auth', 'role:admin|HR|secretary_general'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
    Route::resource('locations', LocationController::class)->except(['show']);
    Route::resource('document-types', DocumentTypeController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('work-shifts', WorkShiftController::class)->except(['show']);

    // --- START: مسارات الإدارة الجديدة ---
    Route::resource('holidays', OfficialHolidayController::class)->except(['show']);
    Route::get('overtime-approvals', [OvertimeApprovalController::class, 'index'])->name('overtime.approvals.index');
    Route::put('overtime-approvals/{overtimeRequest}/approve', [OvertimeApprovalController::class, 'approve'])->name('overtime.approvals.approve');
    Route::put('overtime-approvals/{overtimeRequest}/reject', [OvertimeApprovalController::class, 'reject'])->name('overtime.approvals.reject');
    // --- END: مسارات الإدارة الجديدة ---

    // Reports & Balances
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-attendance', [ReportController::class, 'exportAttendance'])->name('reports.export.attendance');
    Route::get('reports/export-balances', [ReportController::class, 'exportBalances'])->name('reports.export.balances');
    Route::get('reports/export-employees', [ReportController::class, 'exportEmployees'])->name('reports.export.employees');
    Route::get('reports/export-leaves', [ReportController::class, 'exportLeaves'])->name('reports.export.leaves');
    Route::get('balances', [BalanceController::class, 'index'])->name('balances.index');
    Route::get('/overtime-approvals', [OvertimeApprovalController::class, 'index'])->name('overtime.approvals.index');
    Route::post('/overtime-approvals/{overtimeRequest}', [OvertimeApprovalController::class, 'processApproval'])->name('overtime.approvals.process');
});

// --- START: مسار تقرير المالية (جديد) ---
Route::middleware(['auth', 'can:view-finance-reports'])->group(function () {
    Route::get('finance/overtime-report', [OvertimeReportController::class, 'index'])->name('finance.overtime.report');
});
// --- END: مسار تقرير المالية (جديد) ---


// HR Only Routes (Kept as is from original file)
Route::middleware(['auth', 'role:HR'])->prefix('HR')->name('HR.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    // ... other HR routes
});

require __DIR__.'/auth.php';
