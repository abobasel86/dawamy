<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Models\WebAuthnCredential;

class WebAuthnCredentialController
{
    public function destroy(Request $request, WebAuthnCredential $credential): RedirectResponse
    {
        if (!$credential->authenticatable || !$credential->authenticatable->is($request->user())) {
            abort(403);
        }

        $credential->delete();

        return back()->with('status', 'passkey-deleted');
    }
}
