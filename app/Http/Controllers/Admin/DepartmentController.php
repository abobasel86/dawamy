<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('manager')->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['manager', 'admin']))->get();
        return view('admin.departments.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'manager_id' => 'nullable|exists:users,id',
            'requires_assistant_approval' => 'sometimes|boolean',
            'allow_cross_delegation' => 'sometimes|boolean', // <-- التحقق من الحقل الجديد
        ]);
        
        Department::create($request->all());
        return redirect()->route('admin.departments.index')->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(Department $department)
    {
        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['manager', 'admin']))->get();
        return view('admin.departments.edit', compact('department', 'managers'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'manager_id' => 'nullable|exists:users,id',
            'requires_assistant_approval' => 'sometimes|boolean',
            'allow_cross_delegation' => 'sometimes|boolean',
        ]);
        
        $department->update([
            'name' => $request->name,
            'manager_id' => $request->manager_id,
            'requires_assistant_approval' => $request->has('requires_assistant_approval'),
            'allow_cross_delegation' => $request->has('allow_cross_delegation'), // <-- حفظ الحقل الجديد
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'تم حذف القسم بنجاح.');
    }
}
