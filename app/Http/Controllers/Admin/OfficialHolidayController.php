<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficialHoliday;
use Illuminate\Http\Request;

class OfficialHolidayController extends Controller
{
    public function index()
    {
        $holidays = OfficialHoliday::latest()->paginate(10);
        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:official_holidays,date',
        ]);

        OfficialHoliday::create($request->all());

        return redirect()->route('admin.holidays.index')->with('success', 'تمت إضافة العطلة الرسمية بنجاح.');
    }

    public function edit(OfficialHoliday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, OfficialHoliday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:official_holidays,date,' . $holiday->id,
        ]);

        $holiday->update($request->all());

        return redirect()->route('admin.holidays.index')->with('success', 'تم تحديث العطلة الرسمية بنجاح.');
    }

    public function destroy(OfficialHoliday $holiday)
    {
        $holiday->delete();
        return redirect()->route('admin.holidays.index')->with('success', 'تم حذف العطلة الرسمية بنجاح.');
    }
}