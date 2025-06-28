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
        // المكتبة تتولى كل شيء، بما في ذلك جلب المستخدم الحالي
        return WebAuthn::attestation();
    }

    /**
     * (الخطوة 2 من عملية التسجيل)
     * يتحقق من صحة بيانات الاعتماد الجديدة ويحفظها تلقائياً.
     */
    public function verifyRegistration(AttestedRequest $request): array
    {
        // دالة save() تقوم بالتحقق من صحة الطلب وحفظ الجهاز في قاعدة البيانات
        $request->save();

        return ['verified' => true];
    }
}