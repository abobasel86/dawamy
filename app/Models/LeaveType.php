<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeaveType extends Model
{
    use HasFactory;
    
    // إضافة الحقل الجديد
    protected $fillable = [
        'name', 
        'days_annually', 
        'unit', 
        'requires_attachment', 
        'is_annual', 
        'requires_delegation',
        'show_in_balance',
        'show_taken_in_report', // <-- أضف هذا السطر
    ];

    // casts to boolean
    protected $casts = [
        'requires_attachment' => 'boolean',
        'is_annual' => 'boolean',
        'requires_delegation' => 'boolean',
        'show_in_balance' => 'boolean',
        'show_taken_in_report' => 'boolean', // <-- أضف هذا السطر
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'leave_type_user')
                    ->withPivot('balance')
                    ->withTimestamps();
    }
}
