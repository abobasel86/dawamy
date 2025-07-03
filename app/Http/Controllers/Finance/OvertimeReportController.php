<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Http\Request;

class OvertimeReportController extends Controller
{
    public function index(Request $request)
    {
        // الفلاتر
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $selectedUser = $request->input('user_id');

        // جلب الطلبات الموافق عليها فقط
        $query = OvertimeRequest::where('status', 'approved')
            ->whereYear('date', Carbon::parse($selectedMonth)->year)
            ->whereMonth('date', Carbon::parse($selectedMonth)->month);

        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }
        
        $overtimeRecords = $query->with('user')->latest()->get();
        
        // جلب قائمة الموظفين لفلتر البحث
        $employees = User::orderBy('name')->get();

        return view('finance.reports.overtime', compact('overtimeRecords', 'employees', 'selectedMonth', 'selectedUser'));
    }
}