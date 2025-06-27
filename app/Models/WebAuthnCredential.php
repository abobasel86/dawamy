<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * This overrides Laravel's default pluralisation which would
     * otherwise expect `web_authn_credentials`.
     */
    protected $table = 'webauthn_credentials';

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
