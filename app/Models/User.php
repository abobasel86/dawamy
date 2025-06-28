<?php

namespace App\Models;

// 1. إضافة هذه الأسطر لاستيراد الأدوات اللازمة من المكتبة
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use NotificationChannels\WebPush\HasPushSubscriptions;

// 2. تعديل تعريف الكلاس ليستخدم الواجهة (Contract)
class User extends Authenticatable implements WebAuthnAuthenticatable
{
    // 3. استخدام الـ Trait الذي يضيف كل الدوال اللازمة تلقائيًا
    use WebAuthnAuthentication;

    use HasFactory, Notifiable, HasRoles, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'probation_end_date' => 'date',
            'permanent_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // --- بقية الدوال والعلاقات في الموديل تبقى كما هي ---
    
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
        if ($leaveType->is_annual) {
            if ($this->employment_status == 'probation') {
                return $this->_getProbationAnnualBalance($leaveType);
            }
            return $this->_getCumulativeAnnualBalance($leaveType);
        }
        return $this->_getStandardLeaveBalance($leaveType);
    }
    
    private function _getProbationAnnualBalance(LeaveType $leaveType): float
    {
        $probationEntitlement = 3; 

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

    private function _getCumulativeAnnualBalance(LeaveType $leaveType): float
    {
        $openingBalancePivot = $this->leaveBalances()->where('leave_type_id', $leaveType->id)->first();
        if (!$openingBalancePivot) {
            return 0;
        }

        $customOpeningBalance = $openingBalancePivot->pivot->balance;
        $balanceSetYear = Carbon::parse($openingBalancePivot->pivot->updated_at)->year;
        $currentBalance = $customOpeningBalance;
        $annualEntitlement = 30; 

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

        $currentBalance -= $takenLeavesByYear->get($balanceSetYear, 0);

        for ($year = $balanceSetYear + 1; $year <= today()->year; $year++) {
            $currentBalance += $annualEntitlement;
            $currentBalance -= $takenLeavesByYear->get($year, 0);
        }

        return min($currentBalance, 90);
    }

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
    
    public function getCurrentStatus()
    {
        $on_leave = $this->leaveRequests()
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        if ($on_leave) {
            return [
                'status' => 'إجازة',
                'time' => $on_leave->leaveType->name,
                'class' => 'text-blue-600 font-bold'
            ];
        }

        $attendance_log = $this->attendanceLogs()
            ->whereDate('punch_in_time', today())
            ->latest()
            ->first();
        
        if ($attendance_log) {
            if ($attendance_log->punch_out_time) {
                return [
                    'status' => 'أنهى دوامه',
                    'time' => \Carbon\Carbon::parse($attendance_log->punch_out_time)->format('h:i A'),
                    'class' => 'text-gray-500'
                ];
            } else {
                return [
                    'status' => 'متواجد',
                    'time' => \Carbon\Carbon::parse($attendance_log->punch_in_time)->format('h:i A'),
                    'class' => 'text-green-600 font-bold'
                ];
            }
        }

        return [
            'status' => 'لم يسجل حضور',
            'time' => null,
            'class' => 'text-red-600 font-bold'
        ];
    }
    
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}