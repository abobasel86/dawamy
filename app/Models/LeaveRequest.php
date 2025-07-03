<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'leave_type_id', 'start_date', 'end_date', 'start_time',
        'end_time', 'reason', 'status', 'approved_by', 'delegated_user_id',
        'rejection_reason',
    ];
	
	protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    // --- إضافة دالة جديدة لجلب الموافق الحالي ---
    public function getCurrentPendingApproverAttribute()
    {
        // ابحث عن أول موافقة في السلسلة حالتها "معلقة"
        return $this->approvals()
                    ->where('status', 'pending')
                    ->orderBy('level', 'asc')
                    ->first()
                    ?->approver; // جلب بيانات الموافق
    }
	
	/**
 * دالة ذكية جديدة لتحديد حالة تتبع الطلب بالتفصيل
 */
public function getRequestStatusDetails(): array
{
    // الحالة 1: إذا كان الطلب مرفوضاً
    if ($this->status === 'rejected') {
        // ابحث عن الموافق الذي قام بالرفض
        $rejectedBy = $this->approvals()->where('status', 'rejected')->first()?->approver;
        return [
            'text' => 'مرفوض من: ' . ($rejectedBy->name ?? 'الإدارة'),
            'class' => 'text-red-600 font-bold'
        ];
    }

    // الحالة 2: إذا تمت الموافقة النهائية على الطلب
    if ($this->status === 'approved') {
        return [
            'text' => 'موافق عليه',
            'class' => 'text-green-600 font-bold'
        ];
    }

    // الحالة 3: إذا كان الطلب لا يزال قيد المراجعة
    if ($this->status === 'pending') {
        // ابحث عن أول موافق لا يزال طلبه "معلقاً"
        $pendingApprover = $this->approvals()->where('status', 'pending')->orderBy('level', 'asc')->first()?->approver;
        return [
            'text' => 'بانتظار موافقة: ' . ($pendingApprover->name ?? 'الإدارة'),
            'class' => 'text-yellow-600'
        ];
    }

    // أي حالة أخرى
    return [
        'text' => $this->status,
        'class' => 'text-gray-500'
    ];
}

/**
 * دالة جديدة لحساب المدة وعرضها بشكل منسق (أيام أو ساعات)
 */
public function getDurationForHumans(): string
{
    if ($this->leaveType->unit === 'days') {
        // حساب عدد الأيام للإجازات اليومية
        return $this->start_date->diffInDays($this->end_date) + 1 . ' يوم';
    } else {
        // حساب المدة الزمنية للإجازات الساعية
        $start = \Carbon\Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
        $end = \Carbon\Carbon::parse($this->end_date->format('Y-m-d') . ' ' . $this->end_time);

        // استخدام دالة diff للحصول على الفارق ككائن ثم تنسيقه
        return $start->diff($end)->format('%H:%I'); // ستعرض النتيجة بصيغة HH:MM
    }
}

    // --- العلاقات ---
    public function delegatedUser(): BelongsTo { return $this->belongsTo(User::class, 'delegated_user_id'); }
    public function attachments(): HasMany { return $this->hasMany(LeaveRequestAttachment::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function approvals(): HasMany { return $this->hasMany(Approval::class); }
}