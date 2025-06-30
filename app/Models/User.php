<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use NotificationChannels\WebPush\HasPushSubscriptions;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'manager_id',
        'location_id',
        'department_id',
        'employment_status',
        'hire_date',
        'probation_end_date',
        'permanent_date',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'permanent_date' => 'date',
        'is_active' => 'boolean',
    ];

    // --- Relationships ---
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function team(): HasMany { return $this->hasMany(User::class, 'manager_id'); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function documents(): HasMany { return $this->hasMany(UserDocument::class); }
    public function leaveBalances(): BelongsToMany
    {
        return $this->belongsToMany(LeaveType::class, 'leave_type_user')
                    ->withPivot('balance', 'updated_at')
                    ->withTimestamps();
    }
    
    // --- New Helper Methods ---
    public function getManagerAttribute()
    {
        return $this->directManager ?? $this->department?->manager;
    }

    public static function getSecretaryGeneral()
    {
        return User::role('secretary_general')->first();
    }

    public static function getAssistantSecretaryGeneral()
    {
        return User::role('assistant_secretary_general')->first();
    }

    public function getLeaveBalance(LeaveType $leaveType): float
    {
        // إذا كانت الإجازة سنوية، نطبق عليها سياسات خاصة
        if ($leaveType->is_annual) {
            if ($this->employment_status == 'probation') {
                return $this->_getProbationAnnualBalance($leaveType);
            }
            // للموظف الدائم أو العقد
            return $this->_getCumulativeAnnualBalance($leaveType);
        }

        // للإجازات الأخرى غير السنوية، نطبق عليها السياسة القياسية
        return $this->_getStandardLeaveBalance($leaveType);
    }
	
	
    // ================== الدوال المساعدة الخاصة (للتنظيم) ==================

    /**
     * حساب رصيد الإجازة السنوية للموظف تحت الاختبار
     */
    private function _getProbationAnnualBalance(LeaveType $leaveType): float
    {
        $probationEntitlement = 3; // رصيد فترة الاختبار

        $takenDuringProbation = $this->leaveRequests()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                if ($this->hire_date && $this->probation_end_date) {
                    $query->where('start_date', '>=', $this->hire_date)
                          ->where('end_date', '<=', $this->probation_end_date);
                }
            })->get()->sum(function ($r) {
                return Carbon::parse($r->start_date)->diffInDays(Carbon::parse($r->end_date)) + 1;
            });

        return max(0, $probationEntitlement - $takenDuringProbation);
    }

    /**
     * حساب الرصيد التراكمي للإجازة السنوية (للموظف الدائم أو العقد)
     */
    private function _getCumulativeAnnualBalance(LeaveType $leaveType): float
    {
        $openingBalancePivot = $this->leaveBalances()->where('leave_type_id', $leaveType->id)->first();

        // إذا لم يقم الأدمن بتحديد رصيد افتتاحي، فالرصيد هو صفر
        if (!$openingBalancePivot) {
            return 0;
        }

        $customOpeningBalance = $openingBalancePivot->pivot->balance;
        $balanceSetYear = Carbon::parse($openingBalancePivot->pivot->updated_at)->year;
        $currentBalance = $customOpeningBalance;
        $annualEntitlement = 30; // الرصيد السنوي

        // جلب كل الإجازات المأخوذة مرة واحدة وتجميعها حسب السنة لتحسين الأداء
        $takenLeavesByYear = $this->leaveRequests()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->get()
            ->groupBy(function ($request) {
                return Carbon::parse($request->start_date)->year;
            })
            ->map(function ($requests) {
                return $requests->sum(fn($r) => Carbon::parse($r->start_date)->diffInDays(Carbon::parse($r->end_date)) + 1);
            });

        // خصم الإجازات من سنة الرصيد الافتتاحي
        $currentBalance -= $takenLeavesByYear->get($balanceSetYear, 0);

        // إضافة الرصيد السنوي وخصم المأخوذ لكل سنة تالية
        for ($year = $balanceSetYear + 1; $year <= today()->year; $year++) {
            $currentBalance += $annualEntitlement;
            $currentBalance -= $takenLeavesByYear->get($year, 0);
        }

        // التأكد من أن الرصيد لا يتجاوز 90 يوماً
        return min($currentBalance, 90);
    }

    /**
     * حساب الرصيد للإجازات العادية (غير السنوية)
     */
    private function _getStandardLeaveBalance(LeaveType $leaveType): float
    {
        $otherLeaveBalancePivot = $this->leaveBalances()->where('leave_type_id', $leaveType->id)->first();
        $totalBalance = $otherLeaveBalancePivot ? $otherLeaveBalancePivot->pivot->balance : $leaveType->days_annually;

        $taken = $this->leaveRequests()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->get()->sum(function ($request) use ($leaveType) {
                if ($leaveType->unit === 'days') {
                    return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                }
                return ($request->start_time && $request->end_time) ? (strtotime($request->end_time) - strtotime($request->start_time)) / 3600 : 0;
            });

        return $totalBalance - $taken;
    }
	
	/**
     * دالة جديدة لتحديد حالة الحضور الحالية للموظف
     */
    public function getCurrentStatus()
    {
        // أولاً، نتحقق إذا كان الموظف في إجازة موافق عليها اليوم
        $on_leave = $this->leaveRequests()
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        if ($on_leave) {
            return [
                'status' => 'إجازة',
                'time' => $on_leave->leaveType->name, // يمكن عرض نوع الإجازة
                'class' => 'text-blue-600 font-bold'
            ];
        }

        // ثانياً، نتحقق من سجل الحضور لهذا اليوم
        $attendance_log = $this->attendanceLogs()
            ->whereDate('punch_in_time', today())
            ->latest()
            ->first();
        
        if ($attendance_log) {
            if ($attendance_log->punch_out_time) {
                // الموظف سجل حضور ثم انصرف
                return [
                    'status' => 'أنهى دوامه',
                    'time' => \Carbon\Carbon::parse($attendance_log->punch_out_time)->format('h:i A'),
                    'class' => 'text-gray-500'
                ];
            } else {
                // الموظف سجل حضور ولم ينصرف بعد
                return [
                    'status' => 'متواجد',
                    'time' => \Carbon\Carbon::parse($attendance_log->punch_in_time)->format('h:i A'),
                    'class' => 'text-green-600 font-bold'
                ];
            }
        }

        // أخيراً، إذا لم يكن في إجازة ولم يسجل حضور
        return [
            'status' => 'لم يسجل حضور',
            'time' => null,
            'class' => 'text-red-600 font-bold'
        ];
    }
	
	/**
     * تعريف علاقة المستخدم مع سجلات الحضور الخاصة به
     */
	public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
	
	
        public function updatePushSubscription($endpoint, $key, $token)
{
    return $this->pushSubscriptions()->updateOrCreate(
        ['endpoint' => $endpoint],
        [
            'public_key' => $key,
            'auth_token' => $token,
        ]
    );
}

        public function deletePushSubscription(string $endpoint)
{
    return $this->pushSubscriptions()->where('endpoint', $endpoint)->delete();
}

/**
 * The user's push subscriptions.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function pushSubscriptions()
{
    return $this->hasMany(\NotificationChannels\WebPush\PushSubscription::class);
}

}
