<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OvertimeApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $pendingRequests = OvertimeRequest::where('current_approver_id', $user->id)
                                          ->whereIn('status', ['pending', 'forwarded'])
                                          ->with(['user', 'approvalHistory.approver'])
                                          ->latest()
                                          ->paginate(15);

        return view('admin.approvals.overtime.index', compact('pendingRequests'));
    }

    /**
     * Process the approval or rejection of an overtime request.
     */
    public function processApproval(Request $request, OvertimeRequest $overtimeRequest)
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $approver = Auth::user();
        $decision = $request->input('decision');
        $remarks = $request->input('remarks');

        if ($overtimeRequest->current_approver_id !== $approver->id) {
            return back()->with('error', 'ليس لديك صلاحية لاتخاذ إجراء على هذا الطلب.');
        }

        if (is_null($overtimeRequest->end_time)) {
             return back()->with('error', 'لا يمكن معالجة الطلب قبل تسجيل وقت الانصراف الفعلي.');
        }

        if ($decision === 'reject' && empty($remarks)) {
            return back()->withErrors(['remarks' => 'سبب الرفض إلزامي.'])->withInput();
        }

        DB::transaction(function () use ($overtimeRequest, $approver, $decision, $remarks) {
            
            if ($decision === 'reject') {
                $overtimeRequest->status = 'rejected';
                $overtimeRequest->current_approver_id = null;
                $overtimeRequest->save();

                $overtimeRequest->approvalHistory()->create([
                    'approver_id' => $approver->id,
                    'status'      => 'rejected',
                    'remarks'     => $remarks,
                ]);
            } else { // Approve
                $overtimeRequest->approvalHistory()->create([
                    'approver_id' => $approver->id,
                    'status'      => 'approved_level_' . $overtimeRequest->approval_level,
                    'remarks'     => $remarks,
                ]);

                // **الإصلاح هنا: تمرير المدير الحالي للدالة لتجنب التكرار**
                $nextApprover = $this->getNextApprover($approver, $overtimeRequest->approval_level);

                if ($nextApprover) {
                    $overtimeRequest->status = 'forwarded';
                    $overtimeRequest->approval_level += 1;
                    $overtimeRequest->current_approver_id = $nextApprover->id;
                } else {
                    $overtimeRequest->status = 'approved';
                    $overtimeRequest->current_approver_id = null;
                }
                $overtimeRequest->save();
            }
        });

        return redirect()->route('admin.overtime.approvals.index')->with('success', 'تمت معالجة الطلب بنجاح.');
    }

    /**
     * Helper function to determine the next approver in the hierarchy.
     * This now uses the Spatie/Permission package correctly and avoids self-assignment.
     */
    private function getNextApprover(User $currentApprover, int $currentLevel): ?User
    {
        // Level 1 (Direct Manager) passes to Level 2 (Assistant SG)
        if ($currentLevel === 1) {
            $assistantSG = User::role('assistant_secretary_general')->first();
            // Return if found and not the same person
            if ($assistantSG && $assistantSG->id !== $currentApprover->id) {
                return $assistantSG;
            }
        }

        // If at Level 1 (and no Assistant SG exists) or at Level 2, pass to the Secretary General
        if ($currentLevel === 1 || $currentLevel === 2) {
            $secretaryGeneral = User::role('secretary_general')->first();
            // Return if found and not the same person
            if ($secretaryGeneral && $secretaryGeneral->id !== $currentApprover->id) {
                return $secretaryGeneral;
            }
        }
        
        // No subsequent approver
        return null;
    }
}
