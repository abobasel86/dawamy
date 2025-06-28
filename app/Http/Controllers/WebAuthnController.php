<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Laragear\WebAuthn\Facades\WebAuthn;

class WebAuthnController extends Controller
{
    /**
     * (الخطوة 1 من عملية التسجيل)
     * يُرجع خيارات التسجيل التي سيستخدمها المتصفح.
     */
    public function generateRegistrationOptions(Request $request): Responsable
    {
        return WebAuthn::attestation();
    }

    /**
     * (الخطوة 2 من عملية التسجيل)
     * يتحقق من صحة بيانات الاعتماد الجديدة ويحفظها تلقائياً.
     */
    public function verifyRegistration(AttestedRequest $request): array
    {
        $request->save();

        return ['verified' => true];
    }
    
    /**
     * (خاص بالتحقق عند تسجيل الدخول أو الحضور)
     * يُرجع خيارات التحقق.
     */
    public function generateAuthenticationOptions(Request $request): Responsable
    {
        return WebAuthn::assertion();
    }

    /**
     * (خاص بالتحقق عند تسجيل الدخول أو الحضور)
     * يتحقق من بصمة المستخدم.
     */
    public function verifyAuthentication(AssertedRequest $request): array
    {
        return ['verified' => WebAuthn::check($request)];
    }
}