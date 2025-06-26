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

class ReportController extends Controller
{
    /**
     * عرض صفحة تقارير الحضور مع الفلاتر والنتائج.
     */
    public function index(Request $request)
    {
        // قوائم المساعدة للفلاتر
        $users = User::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        /*
         * =====================================
         *  تقارير الحضور والانصراف
         * =====================================
         */
        $attendanceQuery = AttendanceLog::with('user')->latest('punch_in_time');

        if ($request->filled('start_date')) {
            $attendanceQuery->whereDate('punch_in_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $attendanceQuery->whereDate('punch_in_time', '<=', $request->end_date);
        }
        if ($request->filled('user_ids') && is_array($request->user_ids)) {
            $attendanceQuery->whereIn('user_id', $request->user_ids);
        }

        $logs = $attendanceQuery->paginate(20, ['*'], 'attendance_page')->withQueryString();

        /*
         * =====================================
         *  أرصدة الإجازات
         * =====================================
         */
        $balanceUsersQuery = User::query();
        if ($request->filled('balance_department_id')) {
            $balanceUsersQuery->where('department_id', $request->balance_department_id);
        }
        if ($request->filled('balance_user_ids') && is_array($request->balance_user_ids)) {
            $balanceUsersQuery->whereIn('id', $request->balance_user_ids);
        }
        $balanceUsers = $balanceUsersQuery->get();
        $leaveTypes = LeaveType::where('show_in_balance', true)->get();
        $balanceData = [];
        foreach ($balanceUsers as $user) {
            $userBalances = [];
            foreach ($leaveTypes as $leaveType) {
                $taken = LeaveRequest::where('user_id', $user->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'approved')
                    ->whereYear('start_date', date('Y'))
                    ->get()
                    ->sum(function ($request) use ($leaveType) {
                        if ($leaveType->unit === 'days') {
                            return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                        }
                        return ($request->start_time && $request->end_time) ? (strtotime($request->end_time) - strtotime($request->start_time)) / 3600 : 0;
                    });

                $userBalances[$leaveType->id] = [
                    'balance' => $user->getLeaveBalance($leaveType),
                    'unit' => $leaveType->unit,
                    'taken' => $taken,
                ];
            }

            $balanceData[] = [
                'name' => $user->name,
                'balances' => $userBalances,
            ];
        }

        /*
         * =====================================
         *  جدول الموظفين
         * =====================================
         */
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
        $employees = $employeeQuery->paginate(20, ['*'], 'employee_page')->withQueryString();

        return view('admin.reports.index', [
            'users' => $users,
            'departments' => $departments,
            'locations' => $locations,
            'logs' => $logs,
            'leaveTypes' => $leaveTypes,
            'balanceData' => $balanceData,
            'employees' => $employees,
        ]);
    }

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
}