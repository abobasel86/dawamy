<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use DateTimeZone;

class LocationController extends Controller
{
    public function index() {
        $locations = Location::with('workShift')->paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    public function create() {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $workShifts = WorkShift::where('is_active', true)->get();
        return view('admin.locations.create', compact('timezones', 'workShifts'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'timezone' => 'required|timezone',
            'work_shift_id' => 'nullable|exists:work_shifts,id',
        ]);
        Location::create($request->all());
        return redirect()->route('admin.locations.index')->with('success', 'تمت إضافة الموقع بنجاح.');
    }

    public function edit(Location $location) {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $workShifts = WorkShift::where('is_active', true)->get();
        return view('admin.locations.edit', compact('location', 'timezones', 'workShifts'));
    }

    public function update(Request $request, Location $location) {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'timezone' => 'required|timezone',
            'work_shift_id' => 'nullable|exists:work_shifts,id',
        ]);
        $location->update($request->all());
        return redirect()->route('admin.locations.index')->with('success', 'تم تحديث الموقع بنجاح.');
    }

    public function destroy(Location $location) {
        $location->delete();
        return redirect()->route('admin.locations.index')->with('success', 'تم حذف الموقع بنجاح.');
    }
}