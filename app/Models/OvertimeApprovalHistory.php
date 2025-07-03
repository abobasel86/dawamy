<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_request_id',
        'approver_id',
        'status',
        'remarks', // هذا الحقل سيحتوي على سبب الرفض
    ];

    /**
     * Get the overtime request associated with the history record.
     */
    public function overtimeRequest()
    {
        return $this->belongsTo(OvertimeRequest::class);
    }

    /**
     * Get the user who made the approval decision.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}