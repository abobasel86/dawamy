<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // مهم جداً لإدارة الـ Transactions

class OvertimeRequestController extends Controller
{
    /**
     * عرض صفحة موافقات العمل الإضافي
     * ستعرض فقط الطلبات التي تنتظر موافقة المدير الحالي
     */
    public function index()
    {
        $user = Auth::user();
        $pendingRequests = OvertimeRequest::where('current_approver_id', $user->id)
                                          ->whereIn('status', ['pending', 'forwarded'])
                                          ->with('user') // لجلب معلومات الموظف صاحب الطلب
                                          ->latest()
                                          ->get();
        
        // هنا يتم إرجاع الواجهة مع البيانات
        return view('overtime.approvals', compact('pendingRequests'));
    }

    /**
     * معالجة قرار الموافقة أو الرفض على طلب العمل الإضافي
     */
    public function processApproval(Request $request, OvertimeRequest $overtimeRequest)
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'required_if:decision,reject|string|max:1000', // سبب الرفض إلزامي عند الرفض
        ]);

        $approver = Auth::user();
        $decision = $request->input('decision');
        $remarks = $request->input('remarks');

        // 1. التحقق من الصلاحية (هل المستخدم الحالي هو المدير المخول؟)
        if ($overtimeRequest->current_approver_id !== $approver->id) {
            return back()->with('error', 'ليس لديك صلاحية لاتخاذ إجراء على هذا الطلب.');
        }

        // 2. التحقق من شرطك: لا يمكن اتخاذ قرار إلا بعد تسجيل وقت الانصراف
        if (is_null($overtimeRequest->end_time)) {
             return back()->with('error', 'لا يمكن معالجة الطلب قبل تسجيل وقت الانصراف الفعلي.');
        }

        // استخدام Transaction لضمان تنفيذ كل العمليات معاً أو لا شيء
        DB::transaction(function () use ($overtimeRequest, $approver, $decision, $remarks) {
            
            if ($decision === 'reject') {
                // في حالة الرفض
                $overtimeRequest->status = 'rejected';
                $overtimeRequest->current_approver_id = null; // إنهاء مسار الموافقة
                $overtimeRequest->save();

                // تسجيل الرفض في سجل التتبع
                $overtimeRequest->approvalHistory()->create([
                    'approver_id' => $approver->id,
                    'status'      => 'rejected',
                    'remarks'     => $remarks, // حفظ سبب الرفض
                ]);

                // إرسال إشعار للموظف بالرفض
                // $overtimeRequest->user->notify(...)

            } else { // في حالة الموافقة
                
                // تسجيل الموافقة الحالية في سجل التتبع
                $overtimeRequest->approvalHistory()->create([
                    'approver_id' => $approver->id,
                    'status'      => 'approved_level_' . $overtimeRequest->approval_level,
                    'remarks'     => $remarks,
                ]);

                // البحث عن المدير التالي في التسلسل
                $nextApprover = $this->getNextApprover($approver, $overtimeRequest->approval_level);

                if ($nextApprover) {
                    // إذا وجد مدير تالٍ، يتم تمرير الطلب إليه
                    $overtimeRequest->status = 'forwarded'; // تغيير الحالة إلى "تم تمريره"
                    $overtimeRequest->approval_level += 1;
                    $overtimeRequest->current_approver_id = $nextApprover->id;
                    
                    // إرسال إشعار للمدير التالي
                    // $nextApprover->notify(...)
                } else {
                    // إذا لم يوجد، فهذا هو آخر مستوى والموافقة نهائية
                    $overtimeRequest->status = 'approved';
                    $overtimeRequest->current_approver_id = null; // إنهاء مسار الموافقة
                    
                    // إرسال إشعار للموظف بالموافقة النهائية
                    // $overtimeRequest->user->notify(...)
                }
                $overtimeRequest->save();
            }
        });

        return redirect()->route('overtime.approvals.index')->with('success', 'تمت معالجة الطلب بنجاح.');
    }

    /**
     * دالة مساعدة لتحديد المدير التالي في تسلسل الموافقات
     * (مدير مباشر -> أمين عام مساعد -> أمين عام)
     */
    private function getNextApprover(User $currentApprover, int $currentLevel): ?User
    {
        // افتراض: لديك حقل 'role' في جدول المستخدمين لتحديد الأدوار
        // أو أي طريقة أخرى لتحديد الأمين العام المساعد والأمين العام
        $assistantSG = User::where('role', 'assistant_secretary_general')->first();
        $secretaryGeneral = User::where('role', 'secretary_general')->first();

        // من المدير المباشر (المستوى 1) إلى الأمين العام المساعد
        if ($currentLevel === 1 && $assistantSG) {
            return $assistantSG;
        }

        // من المدير المباشر (المستوى 1) إلى الأمين العام مباشرة (في حال عدم وجود مساعد)
        // أو من الأمين العام المساعد (المستوى 2) إلى الأمين العام
        if (($currentLevel === 1 || $currentLevel === 2) && $secretaryGeneral) {
            // نتأكد أنه ليس نفس الشخص (في حال كان المدير المباشر هو الأمين العام مثلاً)
            if ($currentApprover->id !== $secretaryGeneral->id) {
                return $secretaryGeneral;
            }
        }
        
        // لا يوجد مدير تالٍ
        return null;
    }
}