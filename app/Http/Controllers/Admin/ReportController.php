<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\Department;
use App\Models\Location;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\OvertimeRequest;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch common data for filters
        $users = User::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $leaveTypes = LeaveType::where('show_in_balance', true)->get();

        // 2. Initialize variables for all possible reports
        $logs = collect();
        $overtimeRequests = collect();
        $leaves = collect();
        $balanceData = [];
        $employees = collect();
        
        $activeTab = $request->input('tab', 'attendance');

        // 3. Populate data based on the active tab
        if ($activeTab === 'attendance') {
            $query = AttendanceLog::with('user')->latest('punch_in_time');
            if ($request->filled('user_ids')) { $query->whereIn('user_id', $request->user_ids); }
            if ($request->filled('start_date')) { $query->whereDate('punch_in_time', '>=', $request->start_date); }
            if ($request->filled('end_date')) { $query->whereDate('punch_in_time', '<=', $request->end_date); }
            $logs = $query->paginate(20, ['*'], 'attendance_page')->withQueryString();
        } 
        if ($logs->isNotEmpty()) {
                $userIds = $logs->pluck('user_id')->unique();
                $dates = $logs->pluck('punch_in_time')->map(fn($date) => $date->format('Y-m-d'))->unique();

                // جلب كل طلبات الإضافي المحتملة في استعلام واحد
                $allOvertimes = OvertimeRequest::whereIn('user_id', $userIds)
                                               ->whereIn('date', $dates)
                                               ->with('approvalHistory.approver')
                                               ->get();

                // ربط الطلبات بالسجلات الصحيحة
                $logs->getCollection()->transform(function ($log) use ($allOvertimes) {
                    $log->overtimes = $allOvertimes->filter(function ($overtime) use ($log) {
                        // مطابقة الإضافي المبكر (وقت بدء الإضافي = وقت الحضور)
                        if (str_contains($overtime->reason, 'مبكر') && $overtime->start_time->equalTo($log->punch_in_time)) {
                            return true;
                        }
                        
                        // مطابقة الإضافي المسائي (وقت نهاية الإضافي = وقت الانصراف)
                        if ($log->punch_out_time && str_contains($overtime->reason, 'مسائي') && $overtime->end_time->equalTo($log->punch_out_time)) {
                            return true;
                        }

                        // مطابقة الإضافي العام (وقت بدء الإضافي = وقت الحضور)
                        if (str_contains($log->status, 'عمل إضافي') && $overtime->start_time->equalTo($log->punch_in_time)) {
                            return true;
                        }

                        return false;
                    });
                    return $log;
                });
            }
        elseif ($activeTab === 'leaves') {
            $query = LeaveRequest::with(['user.department', 'user.location', 'leaveType', 'approvals.approver']);
            if ($request->filled('leave_user_ids')) { $query->whereIn('user_id', $request->leave_user_ids); }
            if ($request->filled('leave_department_id')) { $query->whereHas('user', fn($q) => $q->where('department_id', $request->leave_department_id)); }
            if ($request->filled('leave_location_id')) { $query->whereHas('user', fn($q) => $q->where('location_id', $request->leave_location_id)); }
            if ($request->filled('leave_start_date')) { $query->where('start_date', '>=', $request->leave_start_date); }
            if ($request->filled('leave_end_date')) { $query->where('end_date', '<=', $request->leave_end_date); }
            $leaves = $query->latest()->paginate(20, ['*'], 'leaves_page')->withQueryString();
        }
        elseif ($activeTab === 'balances') {
             $query = User::query();
            if ($request->filled('balance_department_id')) { $query->where('department_id', $request->balance_department_id); }
            if ($request->filled('balance_user_ids')) { $query->whereIn('id', $request->balance_user_ids); }
            $balanceUsers = $query->get();
            foreach ($balanceUsers as $user) {
                $userBalances = [];
                foreach ($leaveTypes as $leaveType) {
                    $taken = $user->leaveRequests()
                        ->where('leave_type_id', $leaveType->id)
                        ->where('status', 'approved')
                        ->whereYear('start_date', date('Y'))
                        ->get()
                        ->sum(function($lr) use ($leaveType) {
                            return $lr->getDurationInHoursOrDays();
                        });
                    $userBalances[$leaveType->id] = ['balance' => $user->getLeaveBalance($leaveType), 'unit' => $leaveType->unit, 'taken' => $taken];
                }
                $balanceData[] = ['name' => $user->name, 'balances' => $userBalances];
            }
        }
        elseif ($activeTab === 'employees') {
            $query = User::with(['department', 'location'])->orderBy('name');
            if ($request->filled('emp_name')) { $query->where('name', 'like', '%' . $request->emp_name . '%'); }
            if ($request->filled('emp_department_id')) { $query->where('department_id', $request->emp_department_id); }
            if ($request->filled('emp_location_id')) { $query->where('location_id', $request->emp_location_id); }
            $employees = $query->paginate(20, ['*'], 'employee_page')->withQueryString();
        }

        // 4. Return the single view with all possible data
        return view('admin.reports.index', compact(
            'users', 'departments', 'locations', 'leaveTypes', 'activeTab',
            'logs', 'overtimeRequests', 'leaves', 'balanceData', 'employees'
        ));
    }

    // ... The rest of the export functions (exportAttendance, exportBalances, exportEmployees, exportLeaves)

    /**
     * تصدير سجلات الحضور المفلترة إلى ملف CSV.
     */
    public function exportAttendance(Request $request)
    {
        // التحقق من وجود تواريخ
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', '2000-01-01');
        $endDate = $request->input('end_date', now()->toDateString());

        // بناء الاستعلام مع نفس منطق الفلترة في دالة index
        $query = AttendanceLog::with('user')
            ->whereBetween('punch_in_time', [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
        
        if ($request->filled('user_ids') && is_array($request->user_ids)) {
            $query->whereIn('user_id', $request->user_ids);
        }

        $logs = $query->orderBy('punch_in_time', 'asc')->get();

        // إعدادات ملف CSV
        $fileName = 'attendance_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // لدعم اللغة العربية في Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 

            // إضافة العناوين الرئيسية للأعمدة
            fputcsv($file, ['ID الموظف', 'اسم الموظف', 'تاريخ الحضور', 'وقت الحضور', 'IP الحضور', 'وقت الانصراف', 'IP الانصراف', 'مدة العمل (ساعات)']);

            foreach ($logs as $log) {
                $duration = 'N/A';
if ($log->punch_out_time) {
    $punchIn = new \DateTime($log->punch_in_time);
    $punchOut = new \DateTime($log->punch_out_time);
    $interval = $punchIn->diff($punchOut);
    $hours = str_pad($interval->h, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad($interval->i, 2, '0', STR_PAD_LEFT);
    $duration = "$hours:$minutes";
                }
                fputcsv($file, [
                    $log->user->id,
                    $log->user->name,
                    \Carbon\Carbon::parse($log->punch_in_time)->format('Y-m-d'),
                    \Carbon\Carbon::parse($log->punch_in_time)->format('h:i:s A'),
                    $log->punch_in_ip_address,
                    $log->punch_out_time ? \Carbon\Carbon::parse($log->punch_out_time)->format('h:i:s A') : 'N/A',
                    $log->punch_out_ip_address,
                    (string) $duration,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * تصدير تقرير أرصدة الإجازات المفلترة إلى ملف CSV.
     */
    public function exportBalances(Request $request)
    {
        $balanceUsersQuery = User::query();

        if ($request->filled('balance_department_id')) {
            $balanceUsersQuery->where('department_id', $request->balance_department_id);
        }

        if ($request->filled('balance_user_ids') && is_array($request->balance_user_ids)) {
            $balanceUsersQuery->whereIn('id', $request->balance_user_ids);
        }

        $users = $balanceUsersQuery->orderBy('name')->get();
        $leaveTypes = LeaveType::where('show_in_balance', true)->get();

        $fileName = 'balances_report_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function () use ($users, $leaveTypes) {
            $file = fopen('php://output', 'w');

            // لدعم اللغة العربية في Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $header = ['اسم الموظف'];
            foreach ($leaveTypes as $type) {
                $header[] = $type->name . ' - الرصيد المتبقي';
                if ($type->show_taken_in_report) {
                    $header[] = $type->name . ' - المأخوذ هذا العام';
                }
            }
            fputcsv($file, $header);

            foreach ($users as $user) {
                $row = [$user->name];
                foreach ($leaveTypes as $type) {
                    $taken = LeaveRequest::where('user_id', $user->id)
                        ->where('leave_type_id', $type->id)
                        ->where('status', 'approved')
                        ->whereYear('start_date', date('Y'))
                        ->get()
                        ->sum(function ($request) use ($type) {
                            if ($type->unit === 'days') {
                                return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                            }
                            return ($request->start_time && $request->end_time) ? (strtotime($request->end_time) - strtotime($request->start_time)) / 3600 : 0;
                        });

                    $row[] = $user->getLeaveBalance($type);
                    if ($type->show_taken_in_report) {
                        $row[] = $taken;
                    }
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * تصدير تقرير الموظفين المفلتر إلى ملف CSV.
     */
    public function exportEmployees(Request $request)
    {
        $employeeQuery = User::with(['department', 'location'])->orderBy('name');

        if ($request->filled('emp_name')) {
            $employeeQuery->where('name', 'like', '%' . $request->emp_name . '%');
        }
        if ($request->filled('emp_department_id')) {
            $employeeQuery->where('department_id', $request->emp_department_id);
        }
        if ($request->filled('emp_location_id')) {
            $employeeQuery->where('location_id', $request->emp_location_id);
        }

        $employees = $employeeQuery->get();

        $fileName = 'employees_report_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');

            // لدعم اللغة العربية في Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['الاسم', 'البريد الإلكتروني', 'القسم', 'الموقع', 'تاريخ التعيين', 'تاريخ التثبيت']);

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->name,
                    $emp->email,
                    optional($emp->department)->name,
                    optional($emp->location)->name,
                    optional($emp->hire_date)?->format('Y-m-d'),
                    optional($emp->permanent_date)?->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * NEW: Export filtered leave requests to a CSV file.
     */
    public function exportLeaves(Request $request)
    {
        $leavesQuery = LeaveRequest::with(['user.department', 'leaveType', 'approvals.approver']);
        
        // Re-apply filters for export
        if ($request->filled('leave_user_ids')) { $leavesQuery->whereIn('user_id', $request->leave_user_ids); }
        if ($request->filled('leave_department_id')) { $leavesQuery->whereHas('user', fn($q) => $q->where('department_id', $request->leave_department_id)); }
        if ($request->filled('leave_location_id')) { $leavesQuery->whereHas('user', fn($q) => $q->where('location_id', $request->leave_location_id)); }
        if ($request->filled('leave_start_date')) { $leavesQuery->where('start_date', '>=', $request->leave_start_date); }
        if ($request->filled('leave_end_date')) { $leavesQuery->where('end_date', '<=', $request->leave_end_date); }
        
        $leaves = $leavesQuery->latest()->get();

        $fileName = 'leaves_report_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($leaves) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // For UTF-8 BOM
            
            // Add all requested columns to the export file
            fputcsv($file, [
                'اسم الموظف', 'القسم', 'نوع الإجازة', 'السبب', 'تاريخ التقديم', 
                'تاريخ البدء', 'تاريخ الانتهاء', 'المدة', 'الحالة النهائية', 'تتبع الطلب', 'سبب الرفض'
            ]);

            foreach ($leaves as $leave) {
                fputcsv($file, [
                    $leave->user->name ?? '',
                    optional($leave->user->department)->name ?? '',
                    $leave->leaveType->name ?? '',
                    $leave->reason,
                    $leave->created_at->format('Y-m-d'),
                    $leave->start_date->format('Y-m-d'),
                    $leave->end_date->format('Y-m-d'),
                    $leave->getDurationForHumans(),
                    $leave->status,
                    $leave->getRequestStatusDetails()['text'],
                    $leave->rejection_reason ?? ''
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}