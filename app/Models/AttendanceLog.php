<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'punch_in_time' => 'datetime',
        'punch_out_time' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'punch_in_time',
        'punch_out_time',
        'punch_in_ip_address',
        'punch_out_ip_address',
        'punch_in_user_agent',
        'punch_out_user_agent',
        'punch_in_selfie_path',
        'punch_out_selfie_path',
        // --- START: الحقول الجديدة التي يجب إضافتها ---
        'lateness_minutes',
        'justification',
        'status',
        // --- END: الحقول الجديدة التي يجب إضافتها ---
    ];

    /**
     * Get the user that owns the attendance log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
