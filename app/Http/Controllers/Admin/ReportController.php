<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\User; // <-- إضافة موديل المستخدم لجلب قائمة الموظفين
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * عرض صفحة تقارير الحضور مع الفلاتر والنتائج.
     */
    public function index(Request $request)
    {
        // 1. جلب قائمة الموظفين لعرضها في الفلتر
        $users = User::orderBy('name')->get();

        // 2. بناء استعلام الحضور والانصراف
        $query = AttendanceLog::with('user')->latest('punch_in_time');

        // 3. تطبيق الفلاتر بناءً على الطلب (request)
        if ($request->filled('start_date')) {
            $query->whereDate('punch_in_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('punch_in_time', '<=', $request->end_date);
        }

        if ($request->filled('user_ids') && is_array($request->user_ids)) {
            $query->whereIn('user_id', $request->user_ids);
        }

        // 4. جلب النتائج مع الترقيم (Pagination)
        // withQueryString() تضمن بقاء الفلاتر عند التنقل بين الصفحات
        $logs = $query->paginate(20)->withQueryString();

        // 5. إرسال البيانات إلى الواجهة
        return view('admin.reports.index', [
            'users' => $users,
            'logs' => $logs,
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
                    $duration = $interval->h + round($interval->i / 60, 2);
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