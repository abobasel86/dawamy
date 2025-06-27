<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;
    
    // إضافة الحقول الجديدة
    protected $fillable = [
        'user_id',
        'punch_in_time',
        'punch_in_ip_address',
        'punch_in_selfie_path',
        'punch_in_user_agent',
        'punch_in_device',
        'punch_in_platform',
        'punch_out_time',
        'punch_out_ip_address',
        'punch_out_selfie_path',
        'punch_out_user_agent',
        'punch_out_device',
        'punch_out_platform',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
