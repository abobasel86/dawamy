<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthnController extends Controller
{
    public function registerCredential(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'credential_id' => 'required',
            'public_key' => 'required',
        ]);

        $request->user()->webauthnCredentials()->create([
            'name' => $request->name,
            'credential_id' => $request->credential_id,
            'public_key' => $request->public_key,
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'credential_id' => 'required',
        ]);

        $valid = $request->user()->webauthnCredentials()
            ->where('credential_id', $request->credential_id)
            ->exists();

        return response()->json(['valid' => $valid]);
    }
}
