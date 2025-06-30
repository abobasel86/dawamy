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
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// WebAuthn Routes
Route::withoutMiddleware(VerifyCsrfToken::class)->group(function () {
    WebAuthnRoutes::register();
});

// Employee Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AttendanceController::class, 'index'])->name('dashboard');
    Route::post('/punch-in', [AttendanceController::class, 'punchIn'])->name('attendance.punchin');
    Route::post('/punch-out', [AttendanceController::class, 'punchOut'])->name('attendance.punchout');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/notifications/{notification}', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
    // مسار لعرض كل الإشعارات
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index'); // <-- أضف هذا السطر
	// مسار لجلب ملخص الإشعارات (العدد الإجمالي غير المقروء + آخر 5)
    Route::get('/notifications-summary', [NotificationController::class, 'summary'])->name('notifications.summary');
    // مسار لتحديد إشعار كمقروء
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push_subscriptions.store');
    Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy'])->name('push_subscriptions.destroy');

    
    // Employee Leave Requests Page
    Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
});

// Manager & Senior Roles Routes for Approvals
Route::middleware(['auth', 'role:manager|admin|secretary_general|assistant_secretary_general|HR'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{approval}', [ApprovalController::class, 'update'])->name('approvals.update');
	Route::get('/team', [TeamController::class, 'index'])->name('team.index');
});

// Admin Only Routes
Route::middleware(['auth', 'role:admin|HR'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
    Route::resource('locations', LocationController::class)->except(['show']);
    Route::resource('document-types', DocumentTypeController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-attendance', [ReportController::class, 'exportAttendance'])->name('reports.export.attendance');
    Route::get('reports/export-balances', [ReportController::class, 'exportBalances'])->name('reports.export.balances');
    Route::get('reports/export-employees', [ReportController::class, 'exportEmployees'])->name('reports.export.employees');
    Route::get('balances', [BalanceController::class, 'index'])->name('balances.index');
});

// HR Only Routes
Route::middleware(['auth', 'role:HR'])->prefix('HR')->name('HR.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-attendance', [ReportController::class, 'exportAttendance'])->name('reports.export.attendance');
    Route::get('reports/export-balances', [ReportController::class, 'exportBalances'])->name('reports.export.balances');
    Route::get('reports/export-employees', [ReportController::class, 'exportEmployees'])->name('reports.export.employees');
    Route::get('balances', [BalanceController::class, 'index'])->name('balances.index');
});
if (app()->environment('local')) {
    Route::get('/test-pure-openssl', function () {
        try {
            // هذا هو نفس الكود تماماً الذي يسبب الانهيار داخل المكتبة
            $keyResource = openssl_pkey_new([
                'curve_name'       => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
            ]);

            if (!$keyResource) {
                return "<h1>فشل: دالة openssl_pkey_new() لم تتمكن من إنشاء المفتاح، ولكن بدون خطأ فادح.</h1>";
            }

        // إذا نجحت الخطوة السابقة، نحاول الحصول على تفاصيل المفتاح
            $details = openssl_pkey_get_details($keyResource);

            if (!$details) {
                return "<h1>فشل: تم إنشاء المفتاح، ولكن لا يمكن قراءة تفاصيله.</h1>";
            }

        // إذا نجح كل شيء، نعرض رسالة النجاح والتفاصيل
            echo "<h1>نجاح باهر!</h1>";
            echo "<p>هذا يعني أن بيئة OpenSSL لديك تعمل بشكل سليم عند استدعائها مباشرة.</p>";
            echo "<p>المشكلة تكمن في مكتبة web-push.</p>";
            dd($details);

        } catch (\Throwable $e) {
            // إذا حدث أي خطأ فادح، نعرضه
            echo "<h1>حدث خطأ فادح عند محاولة استدعاء الدالة مباشرة:</h1>";
            dd($e);
        }
    });
}


require __DIR__.'/auth.php';
