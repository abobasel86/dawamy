<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Approval;
// تأكد من أن هذه الإشعارات موجودة لديك
use App\Notifications\LeaveForwardedToAssistant;
use App\Notifications\LeaveReadyForFinalApproval;
use App\Notifications\LeaveFinalDecision;
use App\Notifications\NewLeaveRequestForApproval;
use App\Notifications\LeaveRequestDecision;

class ApprovalController extends Controller
{
    /**
     * عرض طلبات الإجازة التي تنتظر موافقة المدير الحالي بشكل تسلسلي.
     */
    public function index()
    {
        $approver = Auth::user();
        
        // ======================= الحل النهائي للاستعلام =======================
        // هذا الاستعلام يضمن أن الطلب يظهر فقط لصاحب أقل مستوى موافقة "معلقة"
        $pendingApprovals = Approval::where('approver_id', $approver->id)
            ->where('status', 'pending')
            // الشرط الجديد والمباشر:
            // تحقق من أن مستوى الموافقة الحالي (level) هو نفسه أقل مستوى موافقة 
            // لا يزال "معلقاً" لنفس طلب الإجازة.
            ->whereRaw('level = (SELECT MIN(level) FROM approvals as a2 WHERE a2.leave_request_id = approvals.leave_request_id AND a2.status = ?)', ['pending'])
            ->whereHas('leaveRequest', function ($query) {
                $query->where('status', 'pending');
            })
			->with('leaveRequest.user', 'leaveRequest.leaveType', 'leaveRequest.attachments')
            ->latest()
            ->get();
        // ======================= نهاية الحل النهائي =======================

        $pendingApprovals->each(function ($approval) {
            $employee = $approval->leaveRequest->user;
            $leaveType = $approval->leaveRequest->leaveType;
            $approval->leaveRequest->employee_balance = $employee->getLeaveBalance($leaveType);
        });
            
        return view('manager.approvals.index', compact('pendingApprovals'));
    }

    /**
     * تحديث حالة طلب الموافقة (موافقة أو رفض).
     */
    public function update(Request $request, Approval $approval)
    {
        // التحقق من أن المستخدم الحالي هو بالفعل من يحق له الموافقة على هذا الطلب
        if (Auth::id() !== $approval->approver_id) {
            abort(403, 'غير مصرح لك بالقيام بهذا الإجراء.');
        }

        $request->validate(['status' => 'required|in:approved,rejected']);

        $approval->update(['status' => $request->status]);
		
        $leaveRequest = $approval->leaveRequest;

        if ($request->status == 'rejected') {
            $leaveRequest->update(['status' => 'rejected']);
            // إشعار الموظف بالرفض
            $leaveRequest->user->notify(new LeaveRequestDecision($leaveRequest)); // <-- تفعيل الإشعار
            return redirect()->route('manager.approvals.index')->with('success', 'تم رفض الطلب.');
        }
        
        if ($request->status == 'approved') {
            $nextApproval = $leaveRequest->approvals()
                ->where('level', '>', $approval->level)
                ->orderBy('level', 'asc')
                ->first();

            if ($nextApproval) {
                // إشعار الموافق التالي في السلسلة
                $nextApprover = $nextApproval->approver;
                if ($nextApprover) {
                    $nextApprover->notify(new NewLeaveRequestForApproval($leaveRequest)); // <-- تفعيل الإشعار
                }
            } else {
                // هذه الموافقة النهائية، نرسل إشعاراً للموظف
                $leaveRequest->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id()
                ]);
                // إشعار الموظف بالموافقة النهائية
                $leaveRequest->user->notify(new LeaveRequestDecision($leaveRequest)); // <-- تفعيل الإشعار
            }
        }

        return redirect()->route('manager.approvals.index')->with('success', 'تم تسجيل الإجراء بنجاح.');
    }
}