<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Department;
use App\Models\LeaveType;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $manager = Auth::user();
        $query = User::query();

        if ($manager->hasRole(['secretary_general', 'admin', 'HR'])) {
            // الأمين العام والأدمن يرون كل الموظفين
        } elseif ($manager->hasRole('assistant_secretary_general')) {
            // الأمين العام المساعد يرى موظفي الإدارات التي تتطلب موافقته
            $departmentIds = Department::where('requires_assistant_approval', true)->pluck('id');
            $query->whereIn('department_id', $departmentIds);
        } else {
            // المدير المباشر يرى فريقه فقط
            $managedDepartmentIds = Department::where('manager_id', $manager->id)->pluck('id');
            $query->where(function ($q) use ($manager, $managedDepartmentIds) {
                $q->where('manager_id', $manager->id)
                  ->orWhereIn('department_id', $managedDepartmentIds);
            })->where('id', '!=', $manager->id);
        }

        // تطبيق الفلاتر (للأمين العام والأدمن)
        if ($manager->hasRole(['secretary_general', 'admin', 'HR'])) {
            if ($request->filled('employee_name')) {
                $query->where('name', 'like', '%' . $request->employee_name . '%');
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
        }

        $employees = $query->with([
            'documents.documentType',
            'leaveBalances',
            'department',
            'attendanceLogs' => fn($q) => $q->whereDate('punch_in_time', today()),
            
            // ===== هذا هو السطر الذي تم تصحيحه =====
            'leaveRequests' => fn($q) => $q->where('status', 'approved')->whereYear('start_date', date('Y'))
            // =======================================

        ])->paginate(20)->withQueryString();

        $leaveTypes = LeaveType::where('show_in_balance', true)->get();
        $departments = Department::orderBy('name')->get();

        return view('manager.team.index', compact('employees', 'leaveTypes', 'departments'));
    }
}