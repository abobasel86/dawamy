<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\NewLeaveRequestForApproval;
use App\Notifications\UserDelegated;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::all();
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->with('leaveType', 'attachments', 'delegatedUser')
            ->latest()
            ->paginate(10); 
            
        // --- تعديل جديد: جلب أرصدة الإجازات المحددة فقط ---
        $leaveTypesToShow = LeaveType::where('show_in_balance', true)->get();
        $balances = [];
        if ($leaveTypesToShow->isNotEmpty()) {
            foreach ($leaveTypesToShow as $type) {
                $balances[$type->name] = [
                    'balance' => $user->getLeaveBalance($type),
                    'unit' => $type->unit
                ];
            }
        } 
            
        // --- تعديل جديد: جلب قائمة زملاء العمل بناءً على إعدادات القسم ---
        $colleagues = collect(); 
        $department = $user->department;

        if ($department && $department->allow_cross_delegation) {
            // إذا كان القسم يسمح بالتفويض الخارجي، جلب كل الموظفين الفعالين
            $colleagues = User::where('id', '!=', $user->id)
                                ->where('is_active', true)
                                ->get();
        } elseif ($department) {
            // إذا لم يكن مسموحاً، جلب الزملاء من نفس القسم فقط
            $colleagues = User::where('department_id', $user->department_id)
                                ->where('id', '!=', $user->id)
                                ->where('is_active', true)
                                ->get();
        }

        $annualLeaveType = LeaveType::where('is_annual', true)->first();
        $annualLeaveBalance = 0;
        if ($annualLeaveType) {
            $annualLeaveBalance = $user->getLeaveBalance($annualLeaveType);
        }

        return view('leaves.index', compact('leaveTypes', 'leaveRequests', 'annualLeaveBalance', 'colleagues'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'reason' => 'required|string|max:255',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'delegated_user_id' => 'nullable|exists:users,id', // <-- التحقق من الحقل الجديد
        ]);

        $user = Auth::user();
        $leaveType = LeaveType::find($request->leave_type_id);
		
		// التحقق من ضرورة وجود موظف مفوض
        if ($leaveType->requires_delegation && !$request->filled('delegated_user_id')) {
             return back()->withInput()->with('error', 'هذا النوع من الإجازات يتطلب تحديد موظف مفوض.');
        }

        $startDate = null;
        $endDate = null;
        $startTime = null;
        $endTime = null;

        if ($leaveType->unit == 'days') {
            $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
            $startDate = $request->start_date;
            $endDate = $request->end_date;
        } else { // hours
            $request->validate(['start_date' => 'required|date', 'start_time' => 'required', 'end_time' => 'required|after:start_time']);
            $startDate = $request->start_date;
            $endDate = $request->start_date;
            $startTime = $request->start_time;
            $endTime = $request->end_time;
        }
        
        if ($leaveType->requires_attachment && !$request->hasFile('attachments')) {
             return back()->with('error', 'هذا النوع من الإجازات يتطلب إرفاق ملف.');
        }

        $requestedAmount = ($leaveType->unit == 'days')
            ? Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
            : (strtotime($endTime) - strtotime($startTime)) / 3600;

        $availableBalance = $user->getLeaveBalance($leaveType);

        if ($requestedAmount > $availableBalance) {
            return redirect()->route('leaves.index')->with('error', "رصيدك المتاح من {$leaveType->name} هو {$availableBalance} فقط.");
        }

		// ... (منطق التحقق من الرصيد والتواريخ يبقى كما هو) ...
        $approvers = [];
        $level = 1;
        $addedApproverIds = [];

        // المستوى الأول: المدير المباشر (الفعلي أو مدير القسم)
        $manager = $user->manager; // Uses the new smart accessor
        if (!$manager) {
            return back()->with('error', 'لم يتم تحديد مدير مباشر أو مدير قسم لك. يرجى مراجعة المسؤول.');
        }
        $approvers[] = ['approver_id' => $manager->id, 'level' => $level++];
        $addedApproverIds[] = $manager->id;

        // المستوى الثاني (اختياري): الأمين العام المساعد
        if ($user->department && $user->department->requires_assistant_approval) {
            $assistantSG = User::getAssistantSecretaryGeneral();
            if ($assistantSG && !in_array($assistantSG->id, $addedApproverIds)) {
                $approvers[] = ['approver_id' => $assistantSG->id, 'level' => $level++];
                $addedApproverIds[] = $assistantSG->id;
            }
        }

        // المستوى الأخير: الأمين العام
        $secretaryGeneral = User::getSecretaryGeneral();
        if ($secretaryGeneral) {
            if (!in_array($secretaryGeneral->id, $addedApproverIds)) {
                $approvers[] = ['approver_id' => $secretaryGeneral->id, 'level' => $level];
            }
        } else {
             return back()->with('error', 'لم يتم تحديد "أمين عام" في النظام. يرجى مراجعة المسؤول.');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reason' => $request->reason,
            'delegated_user_id' => $request->delegated_user_id, // حفظ الموظف المفوض
            'status' => 'pending',
        ]);
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $leaveRequest->attachments()->create(['file_path' => $path, 'original_name' => $file->getClientOriginalName()]);
            }
        }
        
        if (!$user->manager_id) {
            return redirect()->route('leaves.index')->with('error', 'لم يتم تحديد مدير مباشر لك. يرجى مراجعة المسؤول.');
        }

        // حفظ سلسلة الموافقات في قاعدة البيانات
        $leaveRequest->approvals()->createMany($approvers);
		
		// إرسال إشعار لأول موافق في السلسلة
        $firstApprover = $leaveRequest->approvals()->orderBy('level', 'asc')->first()?->approver;
        if ($firstApprover) {
            $firstApprover->notify(new NewLeaveRequestForApproval($leaveRequest));
        }

        // إرسال إشعار للموظف المفوض (إذا تم تحديده)
        if ($leaveRequest->delegatedUser) {
            $leaveRequest->delegatedUser->notify(new UserDelegated($leaveRequest));
        }
		
        return redirect()->route('leaves.index')->with('success', 'تم إرسال طلب الإجازة بنجاح.');
    }
}
