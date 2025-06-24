<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    // إضافة الحقل الجديد
    protected $fillable = ['name', 'manager_id', 'requires_assistant_approval', 'allow_cross_delegation'];

    // casts to boolean
    protected $casts = [
        'requires_assistant_approval' => 'boolean',
        'allow_cross_delegation' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
