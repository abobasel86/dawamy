<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::paginate(10);
        return view('admin.leave_types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.leave_types.create');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave_types.edit', compact('leaveType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'days_annually' => 'required|integer|min:0',
            'unit' => 'required|in:days,hours',
            'requires_attachment' => 'sometimes|boolean',
            'is_annual' => 'sometimes|boolean',
            'requires_delegation' => 'sometimes|boolean',
            'show_in_balance' => 'sometimes|boolean', // <-- التحقق من الحقل الجديد
			'show_taken_in_report' => 'sometimes|boolean',
        ]);

        LeaveType::create([
            'name' => $request->name,
            'days_annually' => $request->days_annually,
            'unit' => $request->unit,
            'requires_attachment' => $request->has('requires_attachment'),
            'is_annual' => $request->has('is_annual'),
            'requires_delegation' => $request->has('requires_delegation'),
            'show_in_balance' => $request->has('show_in_balance'), // <-- حفظ الحقل الجديد
			'show_taken_in_report' => $request->has('show_taken_in_report'),
        ]);

        return redirect()->route('admin.leave-types.index')->with('success', 'تمت إضافة نوع الإجازة بنجاح.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveType->id,
            'days_annually' => 'required|integer|min:0',
            'unit' => 'required|in:days,hours',
            'requires_attachment' => 'sometimes|boolean',
            'is_annual' => 'sometimes|boolean',
            'requires_delegation' => 'sometimes|boolean',
            'show_in_balance' => 'sometimes|boolean',
			'show_taken_in_report' => 'sometimes|boolean',
        ]);

        $leaveType->update([
            'name' => $request->name,
            'days_annually' => $request->days_annually,
            'unit' => $request->unit,
            'requires_attachment' => $request->has('requires_attachment'),
            'is_annual' => $request->has('is_annual'),
            'requires_delegation' => $request->has('requires_delegation'),
            'show_in_balance' => $request->has('show_in_balance'),
			'show_taken_in_report' => $request->has('show_taken_in_report'),
        ]);

        return redirect()->route('admin.leave-types.index')->with('success', 'تم تحديث نوع الإجازة بنجاح.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->route('admin.leave-types.index')->with('success', 'تم حذف نوع الإجازة بنجاح.');
    }
}