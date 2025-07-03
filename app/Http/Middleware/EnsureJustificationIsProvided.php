<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJustificationIsProvided
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ السماح دائماً بالوصول لمسارات صفحة المبرر وتسجيل الخروج لتجنب الحلقة
        if ($request->routeIs('justification.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // 🔒 إذا كانت هناك جلسة تحتوي على إجراء معلق، إعادة التوجيه لصفحة المبرر
        if (session()->has('pending_justification')) {
            $pending = session('pending_justification');

            return redirect()->route('justification.create', [
                'type' => $pending['type'],
                'record_id' => $pending['record_id']
            ])->with('info', $pending['message'])
              ->with('error', 'يجب عليك إكمال هذا الإجراء أولاً قبل المتابعة.');
        }

        // ✅ في حال عدم وجود إجراء معلق، يُسمح بالمتابعة
        return $next($request);
    }
}
