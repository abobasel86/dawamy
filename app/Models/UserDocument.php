<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    use HasFactory;

    // تحديث الحقول المسموح بها
    protected $fillable = ['user_id', 'document_type_id', 'file_path', 'original_name'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // إضافة علاقة جديدة مع جدول أنواع المستندات
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
}
