<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AttendanceLog;
use App\Models\WebAuthnCredential;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $latestAttendance = AttendanceLog::where('user_id', $user->id)
            ->whereDate('punch_in_time', Carbon::today())
            ->whereNull('punch_out_time')
            ->first();

        $credentialIds = $user->webauthnCredentials()
            ->pluck('credential_id');

        return view('dashboard', [
            'hasPunchedIn' => $latestAttendance ? true : false,
            'credentialIds' => $credentialIds,
        ]);
    }

    public function history()
    {
        $attendanceLogs = AttendanceLog::where('user_id', Auth::id())
            ->orderBy('punch_in_time', 'desc')
            ->paginate(15);
        return view('attendance.history', compact('attendanceLogs'));
    }

    public function punchIn(Request $request)
    {
        $user = Auth::user();

        if (!$this->verifyWebAuthn($request)) {
            return back()->with('error', 'فشل التحقق البيومتري.');
        }
        
        if ($response = $this->validateGeolocation($request, $user, true)) {
            return $response;
        }
        
        $hasOpenAttendance = AttendanceLog::where('user_id', $user->id)
            ->whereNull('punch_out_time')
            ->exists();

        if ($hasOpenAttendance) {
            return back()->with('error', 'لا يمكنك تسجيل حضور جديد قبل تسجيل الانصراف من الجلسة السابقة.');
        }

        // --- الجزء الجديد: حفظ الصورة وبيانات الجهاز ---
        $selfiePath = $this->storeSelfie($request, $user, 'punch_in_');

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        $device = $agent->device();
        $platform = $agent->platform();

        AttendanceLog::create([
            'user_id' => $user->id,
            'punch_in_time' => now(),
            'punch_in_ip_address' => $request->ip(),
            'punch_in_user_agent' => $request->header('User-Agent'),
            'punch_in_device' => $device,
            'punch_in_platform' => $platform,
            'punch_in_selfie_path' => $selfiePath,
        ]);

        return redirect()->route('dashboard')->with('success', 'تم تسجيل حضورك بنجاح!');
    }

    /**
     * ==== تم تحديث هذه الدالة لتشمل التحقق من الموقع ====
     */
    public function punchOut(Request $request)
    {
        $user = Auth::user();

        if (!$this->verifyWebAuthn($request)) {
            return back()->with('error', 'فشل التحقق البيومتري.');
        }
        
        // --- الجزء الجديد: التحقق من الموقع الجغرافي عند الانصراف ---
        if ($response = $this->validateGeolocation($request, $user)) {
            return $response;
        }
        // --- نهاية الجزء الجديد ---

        $attendanceLog = AttendanceLog::where('user_id', $user->id)
            ->whereNull('punch_out_time')
            ->latest('punch_in_time')
            ->first();

        if (!$attendanceLog) {
            return back()->with('error', 'لم يتم العثور على سجل حضور مفتوح.');
        }

        // --- الجزء الجديد: حفظ الصورة وبيانات الجهاز ---
        $selfiePath = $this->storeSelfie($request, $user, 'punch_out_');

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        $device = $agent->device();
        $platform = $agent->platform();

        $attendanceLog->update([
            'punch_out_time' => now(),
            'punch_out_ip_address' => $request->ip(),
            'punch_out_user_agent' => $request->header('User-Agent'),
            'punch_out_device' => $device,
            'punch_out_platform' => $platform,
            'punch_out_selfie_path' => $selfiePath,
        ]);

        return redirect()->route('dashboard')->with('success', 'تم تسجيل انصرافك بنجاح. يومك سعيد!');
    }

    private function storeSelfie(Request $request, $user, string $prefix): ?string
    {
        if (!$request->has('selfie_image')) {
            return null;
        }

        $imageData = $request->input('selfie_image');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $imageName = $prefix . time() . '.png';

        Storage::disk('public')->put('selfies/' . $user->id . '/' . $imageName, base64_decode($imageData));

        return 'selfies/' . $user->id . '/' . $imageName;
    }

    private function validateGeolocation(Request $request, $user, bool $includeDistance = false)
    {
        $workLocation = $user->location;
        if (!$workLocation) {
            return back()->with('error', 'لم يتم تحديد موقع عمل لك. يرجى مراجعة المسؤول.');
        }

        $userLat = $request->input('latitude');
        $userLon = $request->input('longitude');

        if (!$userLat || !$userLon) {
            return back()->with('error', 'لم نتمكن من تحديد موقعك. يرجى تفعيل خدمات الموقع في متصفحك.');
        }

        $distance = $this->calculateDistance($workLocation->latitude, $workLocation->longitude, $userLat, $userLon);

        if ($distance > $workLocation->radius_meters) {
            $message = $includeDistance
                ? 'أنت خارج نطاق العمل المسموح به. المسافة الحالية: ' . round($distance) . ' متر.'
                : 'أنت خارج نطاق العمل المسموح به لتسجيل الانصراف.';
            return back()->with('error', $message);
        }

        return null;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    private function verifyWebAuthn(Request $request): bool
    {
        $credId = $request->input('credential_id');
        if (!$credId) {
            return false;
        }

        $registered = $request->user()->webauthnCredentials()
            ->pluck('credential_id')
            ->toArray();

        return in_array($credId, $registered, true);
    }
}