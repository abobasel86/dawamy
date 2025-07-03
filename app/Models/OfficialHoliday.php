<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialHoliday extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها بشكل جماعي.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'date',
    ];

    /**
     * تحويل حقل التاريخ إلى كائن Carbon تلقائياً.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];
}