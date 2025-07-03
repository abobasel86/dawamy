<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workShifts = WorkShift::latest()->paginate(10);
        return view('admin.work_shifts.index', compact('workShifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workShift = new WorkShift(); // Pass an empty object for the form
        return view('admin.work_shifts.create', compact('workShift'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'description' => 'nullable|string',
            'grace_period_before_start_minutes' => 'required|integer|min:0',
            'grace_period_after_start_minutes' => 'required|integer|min:0',
            'is_active' => 'nullable', // Changed to nullable to handle checkbox
        ]);

        // Handle checkbox value correctly
        $validated['is_active'] = $request->has('is_active');

        WorkShift::create($validated);

        return redirect()->route('admin.work-shifts.index')->with('success', 'تم إنشاء نمط الدوام بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkShift $workShift)
    {
        return view('admin.work_shifts.edit', compact('workShift'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkShift $workShift)
    {
        // **الإصلاح هنا: إضافة دالة التحقق من الصحة**
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'description' => 'nullable|string',
            'grace_period_before_start_minutes' => 'required|integer|min:0',
            'grace_period_after_start_minutes' => 'required|integer|min:0',
            'is_active' => 'nullable', // Changed to nullable to handle checkbox
        ]);

        // Handle checkbox value correctly
        $validated['is_active'] = $request->has('is_active');

        $workShift->update($validated);

        return redirect()->route('admin.work-shifts.index')->with('success', 'تم تحديث نمط الدوام بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkShift $workShift)
    {
        try {
            $workShift->delete();
            return redirect()->route('admin.work-shifts.index')->with('success', 'تم حذف نمط الدوام بنجاح.');
        } catch (\Exception $e) {
            // This will catch errors if the work shift is linked to other records
            return redirect()->route('admin.work-shifts.index')->with('error', 'لا يمكن حذف نمط الدوام لأنه مرتبط بسجلات أخرى.');
        }
    }
}
