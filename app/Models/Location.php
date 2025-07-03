<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    
    /**
     * الحقول المسموح بتعبئتها بشكل جماعي
     * * @var array
     */
    protected $fillable = [
        'name', 
        'latitude', 
        'longitude', 
        'radius_meters',
        // -- START:  הוסף את השדות האלה  --
        'work_shift_id', 
        'timezone',
        // -- END:  הוסף את השדות האלה  --
    ];

    /**
     * علاقة الموقع مع المستخدمين
     */
    public function users() {
        return $this->hasMany(User::class);
    }

    /**
     * علاقة الموقع مع نمط الدوام
     */
    public function workShift() {
        return $this->belongsTo(WorkShift::class);
    }
}