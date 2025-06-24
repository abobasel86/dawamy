<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        if ($validator->fails()) {
            // إذا فشل التحقق، أرجع الأخطاء للمتصفح بدلاً من خطأ 500
            return response()->json(['message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $endpoint = $validatedData['endpoint'];
        $token = $validatedData['keys']['auth'];
        $key = $validatedData['keys']['p256dh'];

        $user = Auth::user();

        // يفترض أن تكون هذه الدالة موجودة في موديل User
        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 201);
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }
        
        Auth::user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}