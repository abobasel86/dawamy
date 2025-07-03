<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    /**
     * تحليل بصمة الحضور لتحديد الحالة الصحيحة.
     */
    public function analyzePunchIn(AttendanceLog $attendanceLog)
    {
        $attendanceLog->load('user.location.workShift');
        $user = $attendanceLog->user;
        $workShift = $user->location->workShift;
        $punchInTime = $attendanceLog->punch_in_time;
        $timezone = $user->location->timezone;

        $shiftStartTime = Carbon::createFromTimeString($workShift->start_time, $timezone)->setDateFrom($punchInTime);
        $shiftEndTime = Carbon::createFromTimeString($workShift->end_time, $timezone)->setDateFrom($punchInTime);

        $punchInCountToday = AttendanceLog::where('user_id', $user->id)
            ->whereDate('punch_in_time', $punchInTime->format('Y-m-d'))
            ->count();

        if ($punchInCountToday === 1) {
            return $this->handleFirstPunchIn($attendanceLog, $workShift, $punchInTime, $shiftStartTime, $shiftEndTime);
        }

        return $this->handleSubsequentPunchIn($attendanceLog, $punchInCountToday, $punchInTime, $shiftStartTime, $shiftEndTime);
    }

    /**
     * معالجة البصمة الأولى في اليوم
     */
    private function handleFirstPunchIn(AttendanceLog $attendanceLog, $workShift, $punchInTime, $shiftStartTime, $shiftEndTime)
    {
        // Case 1: Punch-in after the shift has completely ended -> Post-shift Overtime
        if ($punchInTime->isAfter($shiftEndTime)) {
            $data = [
                'user_id'    => $attendanceLog->user->id,
                'start_time' => $punchInTime,
                'end_time'   => null,
                'reason'     => 'عمل إضافي بعد الدوام',
            ];
            $overtimeRequest = $this->createOvertimeRequest($data);
            $attendanceLog->update(['status' => "عمل إضافي"]);
            return $this->requiresJustification('post_shift_overtime', $overtimeRequest->id, 'تم تسجيل بصمة عمل إضافي. يرجى تقديم السبب.');
        }

        $status = "دوام نظامي";

        $graceAfterStart = $workShift->grace_period_after_start_minutes ?? 0;
        $shiftStartTimeWithGrace = $shiftStartTime->copy()->addMinutes($graceAfterStart);

        // Case 2: Punch-in after shift starts AND after the grace period -> Lateness
        if ($punchInTime->isAfter($shiftStartTimeWithGrace)) {
            $latenessMinutes = $punchInTime->diffInMinutes($shiftStartTime);
            $attendanceLog->update([
                'lateness_minutes' => $latenessMinutes,
                'status' => $status . " (تأخير صباحي)"
            ]);
            return $this->requiresJustification('lateness', $attendanceLog->id, 'لقد تم تسجيل حضورك متأخراً. يرجى تقديم سبب التأخير.');
        }
        
        $graceBeforeStart = $workShift->grace_period_before_start_minutes ?? 0;
        $gracePeriodStart = $shiftStartTime->copy()->subMinutes($graceBeforeStart);

        // Case 3: Punch-in before the pre-shift grace period -> Pre-shift Overtime
        // **الإصلاح هنا: الشرط الآن يتحقق بشكل صحيح**
        if ($punchInTime->isBefore($gracePeriodStart)) {
            $data = [
                'user_id'    => $attendanceLog->user->id,
                'start_time' => $punchInTime,
                'end_time'   => null,
                'reason'     => 'عمل إضافي مبكر',
            ];
            $overtimeRequest = $this->createOvertimeRequest($data);
            $attendanceLog->update(['status' => $status . " (إضافي مبكر)"]);
            return $this->requiresJustification('pre_shift_overtime', $overtimeRequest->id, 'لقد سجلت حضورك مبكراً. يرجى تقديم سبب العمل الإضافي.');
        }

        // Case 4: Normal punch-in (within any grace period)
        $attendanceLog->update(['status' => $status]);
        return ['status' => 'success'];
    }

    /**
     * معالجة البصمات التالية في نفس اليوم
     */
    private function handleSubsequentPunchIn(AttendanceLog $attendanceLog, int $count, $punchInTime, $shiftStartTime, $shiftEndTime)
    {
        // If punch-in is during official work hours (e.g., returning from a break)
        if ($punchInTime->between($shiftStartTime, $shiftEndTime)) {
            $attendanceLog->update(['status' => "عودة للدوام " . $count]);
            return ['status' => 'success'];
        }

        // If punch-in is outside work hours, it's considered overtime
        $data = [
            'user_id'    => $attendanceLog->user->id,
            'start_time' => $punchInTime,
            'end_time'   => null,
            'reason'     => 'عمل إضافي (بصمة تالية)',
        ];
        $overtimeRequest = $this->createOvertimeRequest($data);
        $attendanceLog->update(['status' => "عمل إضافي " . $count]);
        return $this->requiresJustification('post_shift_overtime', $overtimeRequest->id, 'تم تسجيل بصمة إضافية. يرجى تقديم سبب العمل الإضافي.');
    }

    /**
     * تحليل بصمة الانصراف - نسخة معدلة بالكامل
     */
    public function analyzePunchOut(AttendanceLog $attendanceLog)
    {
        $user = $attendanceLog->user;
        $workShift = $user->location->workShift;
        $punchOutTime = $attendanceLog->punch_out_time;
        $timezone = $user->location->timezone;
        $status = $attendanceLog->status;

        $shiftStartTime = Carbon::createFromTimeString($workShift->start_time, $timezone)->setDateFrom($punchOutTime);
        $shiftEndTime = Carbon::createFromTimeString($workShift->end_time, $timezone)->setDateFrom($punchOutTime);

        // --- NEW LOGIC (V2) ---

        // 1. Handle post-shift overtime first.
        // This is created if the punch-out is after the official shift ends.
        if ($punchOutTime->isAfter($shiftEndTime)) {
            $data = [
                'user_id'    => $user->id,
                'start_time' => $shiftEndTime,
                'end_time'   => $punchOutTime,
                'reason'     => 'عمل إضافي مسائي',
            ];
            $this->createOvertimeRequest($data);
            $attendanceLog->update(['status' => $status . " (مع إضافي مسائي)"]);
        }

        // 2. Handle any open overtime requests from earlier in the day.
        $openOvertime = OvertimeRequest::where('user_id', $user->id)
            ->where('date', $punchOutTime->format('Y-m-d'))
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if ($openOvertime) {
            // Determine the correct end time for this open request.
            $endTimeForOpenRequest = $openOvertime->start_time->isBefore($shiftStartTime)
                ? $shiftStartTime  // If it's pre-shift, it ends when the official shift starts.
                : $punchOutTime;   // Otherwise (e.g., subsequent punch), it ends with this punch-out.

            $openOvertime->update([
                'end_time' => $endTimeForOpenRequest,
                'actual_minutes' => abs($endTimeForOpenRequest->diffInMinutes($openOvertime->start_time)),
            ]);
        }

        return ['status' => 'success'];
    }

    /**
     * دالة لإنشاء طلب إضافي
     */
    public function createOvertimeRequest(array $data): OvertimeRequest
    {
        $employee = User::find($data['user_id']);
        if (!$employee) {
            throw new \Exception('لم يتم تحديد الموظف لإنشاء طلب العمل الإضافي.');
        }
        $directManager = $employee->manager;
        if (!$directManager) {
            throw new \Exception('لا يمكن إنشاء الطلب لعدم وجود مدير مباشر محدد للموظف: ' . $employee->name);
        }
        $startTime = Carbon::parse($data['start_time']);
        $endTime = isset($data['end_time']) ? Carbon::parse($data['end_time']) : null;
        if ($endTime && $endTime->lt($startTime)) {
             $endTime->addDay();
        }
        $actualMinutes = $endTime ? abs($startTime->diffInMinutes($endTime)) : 0;
        return OvertimeRequest::create([
            'user_id'             => $employee->id,
            'date'                => $startTime->format('Y-m-d'),
            'start_time'          => $startTime,
            'end_time'            => $endTime,
            'actual_minutes'      => $actualMinutes,
            'reason'              => $data['reason'] ?? 'Pending Justification',
            'status'              => 'pending',
            'approval_level'      => 1,
            'current_approver_id' => $directManager->id,
        ]);
    }
    
    /**
     * دالة مساعدة لإرجاع طلب التبرير
     */
    private function requiresJustification(string $type, int $recordId, string $message): array
    {
        $result = [
            'status' => 'requires_justification',
            'type' => $type,
            'record_id' => $recordId,
            'message' => $message
        ];
        session(['pending_justification' => $result]);
        return $result;
    }

    /**
     * حفظ السبب الذي تم إدخاله من قبل المستخدم
     */
    public function saveJustification(Request $request)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
                'record_id' => 'required|integer',
                'type' => 'required|string'
            ]);
            if ($validated['type'] === 'lateness') {
                $attendance = AttendanceLog::findOrFail($validated['record_id']);
                $attendance->update(['justification' => $validated['reason']]);
            } else {
                $overtime = OvertimeRequest::findOrFail($validated['record_id']);
                $overtime->update(['reason' => $validated['reason']]);
            }
            return ['status' => 'success', 'message' => 'تم حفظ السبب بنجاح.'];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ['status' => 'error', 'message' => 'لم يتم العثور على السجل المطلوب لحفظ السبب.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'خطأ عام في الخدمة: ' . $e->getMessage()];
        }
    }
}
