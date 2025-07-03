<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AttendanceLog;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $user = Auth::user();
        $latestLog = AttendanceLog::where('user_id', $user->id)
                                  ->latest('punch_in_time')
                                  ->first();
        $hasOpenSession = $latestLog && is_null($latestLog->punch_out_time);
        return view('dashboard', ['hasPunchedIn' => $hasOpenSession]);
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
        $workLocation = $user->location;

        if (!$workLocation || !$workLocation->workShift) {
            return back()->with('error', 'لم يتم تحديد موقع عمل أو نمط دوام لك.');
        }

        $distance = $this->calculateDistance($workLocation->latitude, $workLocation->longitude, $request->latitude, $request->longitude);
        if ($distance > $workLocation->radius_meters) {
            return back()->with('error', 'أنت خارج نطاق العمل المسموح به.');
        }

        if (AttendanceLog::where('user_id', $user->id)->whereNull('punch_out_time')->exists()) {
            return back()->with('error', 'لديك جلسة حضور مفتوحة بالفعل.');
        }
        
        $credentials = $request->only(['id', 'rawId', 'type', 'response']) + ['email' => $user->email];
        if (!Auth::validate($credentials)) {
            return back()->with('error', 'فشل التحقق من البصمة.');
        }

        $selfiePath = $this->storeSelfie($request, 'punch_in');

        $attendanceLog = AttendanceLog::create([
            'user_id' => $user->id,
            'punch_in_time' => now($workLocation->timezone),
            'punch_in_ip_address' => $request->ip(),
            'punch_in_user_agent' => $request->header('User-Agent'),
            'punch_in_selfie_path' => $selfiePath,
        ]);

        $result = $this->attendanceService->analyzePunchIn($attendanceLog);

        if (is_array($result) && $result['status'] === 'requires_justification') {
            return redirect()->route('justification.create', [
                'type' => $result['type'],
                'record_id' => $result['record_id']
            ])->with('info', $result['message']);
        }

        return redirect()->route('dashboard')->with('success', 'تم تسجيل حضورك بنجاح!');
    }

    public function punchOut(Request $request)
    {
        $user = Auth::user();
        $workLocation = $user->location;

        if (!$workLocation || !$workLocation->workShift) {
            return back()->with('error', 'لم يتم تحديد موقع عمل أو نمط دوام لك.');
        }

        $attendanceLog = AttendanceLog::where('user_id', $user->id)
                                      ->whereNull('punch_out_time')
                                      ->latest('punch_in_time')
                                      ->first();
        if (!$attendanceLog) {
            return back()->with('error', 'لم يتم العثور على سجل حضور مفتوح.');
        }
        
        $credentials = $request->only(['id', 'rawId', 'type', 'response']) + ['email' => $user->email];
        if (!Auth::validate($credentials)) {
            return back()->with('error', 'فشل التحقق من البصمة.');
        }

        $selfiePath = $this->storeSelfie($request, 'punch_out');

        $attendanceLog->update([
            'punch_out_time' => now($workLocation->timezone),
            'punch_out_ip_address' => $request->ip(),
            'punch_out_user_agent' => $request->header('User-Agent'),
            'punch_out_selfie_path' => $selfiePath,
        ]);
        
        $result = $this->attendanceService->analyzePunchOut($attendanceLog);
        
        if (is_array($result) && $result['status'] === 'requires_justification') {
            return redirect()->route('justification.create', [
                'type' => $result['type'],
                'record_id' => $result['record_id']
            ])->with('info', $result['message']);
        }
        
        return redirect()->route('dashboard')->with('success', 'تم تسجيل انصرافك بنجاح. يومك سعيد!');
    }

    public function createJustification(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'record_id' => 'required|integer',
        ]);

        return view('attendance.justification', [
            'type' => $validated['type'],
            'record_id' => $validated['record_id'],
        ]);
    }

    public function storeJustification(Request $request)
{
    try {
        $result = $this->attendanceService->saveJustification($request);

        if (is_array($result) && $result['status'] === 'success') {
            // --- START: The Fix ---
            // حذف العلامة من الجلسة بعد نجاح الحفظ
            session()->forget('pending_justification');
            // --- END: The Fix ---
            return redirect()->route('dashboard')->with($result['status'], $result['message']);
        }

        return redirect()->route('dashboard')->with('error', $result['message'] ?? 'حدث خطأ غير متوقع.');

    } catch (\Exception $e) {
        return back()->with('error', 'Error from Service: ' . $e->getMessage());
    }
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
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    private function storeSelfie(Request $request, string $type)
    {
        if (!$request->has('selfie_image')) {
            return null;
        }
        $imageData = $request->input('selfie_image');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $imageName = $type . '_' . time() . '.png';
        $path = 'selfies/' . Auth::id() . '/' . $imageName;
        Storage::disk('public')->put($path, base64_decode($imageData));
        return $path;
    }
}
