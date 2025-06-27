<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'credential_id',
        'public_key',
        'counter',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
