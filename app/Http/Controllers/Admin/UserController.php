<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use App\Models\LeaveType;
use App\Models\UserDocument;
use App\Models\DocumentType;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }
    
    public function store(Request $request)
    {
        // --- تعديل جديد: تبسيط قواعد كلمة المرور ---
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:8'], // تم التبسيط هنا
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // إعطاء المستخدم الجديد دور "موظف" بشكل افتراضي
        $user->assignRole('employee');

        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء حساب الموظف بنجاح.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $managers = User::where('id', '!=', $user->id)->get();
        $locations = Location::all();
        $leaveTypes = LeaveType::all();
        $documentTypes = DocumentType::all();
        $departments = Department::all();

        $userBalances = $user->leaveBalances()->get()->pluck('pivot.balance', 'id');
        $userDocuments = $user->documents()->with('documentType')->get()->keyBy('document_type_id');

        return view('admin.users.edit', compact(
            'user', 'roles', 'managers', 'locations', 
            'leaveTypes', 'userBalances', 'documentTypes', 'userDocuments', 'departments'
        ));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'employment_status' => 'required|in:probation,permanent,contract',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'hire_date' => 'nullable|date',
            'probation_end_date' => 'nullable|date|after_or_equal:hire_date',
            'permanent_date' => 'nullable|date|after_or_equal:probation_end_date',
            'balances' => 'sometimes|array',
            'balances.*.balance' => 'sometimes|integer|min:0',
            'location_id' => 'nullable|exists:locations,id', // <-- أضف هذا السطر للتحقق

        ]);

        $user->syncRoles($request->role);
        
        $user->update($request->only([
            'name',
            'manager_id', 
            'location_id', 
            'department_id',
            'employment_status', 
            'hire_date', 
            'probation_end_date', 
            'permanent_date',
            'is_active',
        ]));

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docTypeId => $file) {
                $oldDoc = $user->documents()->where('document_type_id', $docTypeId)->first();
                if ($oldDoc) {
                    Storage::disk('public')->delete($oldDoc->file_path);
                    $oldDoc->delete();
                }

                $path = $file->store('user_documents/' . $user->id, 'public');
                $user->documents()->create([
                    'document_type_id' => $docTypeId,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }
        }

        if ($request->has('balances')) {
            $balancesToSync = [];
            foreach ($request->balances as $leaveTypeId => $balanceData) {
                if (isset($balanceData['balance'])) {
                    $balancesToSync[$leaveTypeId] = ['balance' => $balanceData['balance']];
                }
            }
            $user->leaveBalances()->sync($balancesToSync);
        }

        return redirect()->route('admin.users.edit', $user)->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }
        
        Storage::disk('public')->deleteDirectory('user_documents/' . $user->id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم حذف الموظف بنجاح.');
    }
}
