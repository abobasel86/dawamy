<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date', // **الإصلاح: إضافة الحقل هنا**
        'start_time',
        'end_time',
        'actual_minutes',
        'reason',
        'status',
        'approval_level',
        'current_approver_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the user who submitted the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current approver for the request.
     */
    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    /**
     * Get the approval history for the request.
     */
    public function approvalHistory()
    {
        return $this->hasMany(OvertimeApprovalHistory::class)->orderBy('created_at');
    }
}
