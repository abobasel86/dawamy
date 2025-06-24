<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- أضف هذا السطر

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // إضافة علاقة جديدة مع جدول ملفات المستخدمين
    public function userDocuments(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }
}
