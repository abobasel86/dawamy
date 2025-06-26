<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Approval;
use App\Notifications\NewLeaveRequestForApproval;
use App\Notifications\LeaveRequestDecision;

class ApprovalController extends Controller
{
    // ... دالة index تبقى كما هي ...
    public function index()
    {
        $approver = Auth::user();
        
        $pendingApprovals = Approval::where('approver_id', $approver->id)
            ->where('status', 'pending')
            ->whereRaw('level = (SELECT MIN(level) FROM approvals as a2 WHERE a2.leave_request_id = approvals.leave_request_id AND a2.status = ?)', ['pending'])
            ->whereHas('leaveRequest', function ($query) {
                $query->where('status', 'pending');
            })
            ->with('leaveRequest.user', 'leaveRequest.leaveType', 'leaveRequest.attachments')
            ->latest()
            ->get();

        $pendingApprovals->each(function ($approval) {
            $employee = $approval->leaveRequest->user;
            $leaveType = $approval->leaveRequest->leaveType;
            if ($employee && $leaveType) {
                 $approval->leaveRequest->employee_balance = $employee->getLeaveBalance($leaveType);
            }
        });
            
        return view('manager.approvals.index', compact('pendingApprovals'));
    }

    public function update(Request $request, Approval $approval)
    {
        if (Auth::id() !== $approval->approver_id) {
            abort(403, 'غير مصرح لك بالقيام بهذا الإجراء.');
        }

        $request->validate(['status' => 'required|in:approved,rejected']);

        $leaveRequest = $approval->leaveRequest;
        $approval->update(['status' => $request->status]);
        
        if ($request->status === 'rejected') {
            $leaveRequest->status = 'rejected';
            $leaveRequest->save();
            // إنشاء الرابط وتمريره
            $url = route('leaves.index');
            $leaveRequest->user->notify(new LeaveRequestDecision($leaveRequest, $url));
            return redirect()->route('manager.approvals.index')->with('success', 'تم رفض الطلب بنجاح.');
        }
        
        $nextApproval = $leaveRequest->approvals()->where('status', 'pending')->orderBy('level', 'asc')->first();

        if ($nextApproval) {
            // إنشاء الرابط وتمريره
            $url = route('manager.approvals.index');
            $nextApproval->approver->notify(new NewLeaveRequestForApproval($leaveRequest, $url));
        } else {
            $leaveRequest->status = 'approved';
            $leaveRequest->approved_by = Auth::id();
            $leaveRequest->save();
            // إنشاء الرابط وتمريره
            $url = route('leaves.index');
            $leaveRequest->user->notify(new LeaveRequestDecision($leaveRequest, $url));
        }

        return redirect()->route('manager.approvals.index')->with('success', 'تم تسجيل الإجراء بنجاح.');
    }
}
