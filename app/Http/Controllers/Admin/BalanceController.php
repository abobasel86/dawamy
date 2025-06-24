<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class BalanceController extends Controller
{
    public function index()
    {
        $users = User::all();
        $leaveTypes = LeaveType::where('show_in_balance', true)->get();
        $balanceData = [];

        foreach ($users as $user) {
            $userBalances = [];
            foreach ($leaveTypes as $leaveType) {
                
                // --- الجزء الجديد: حساب الأيام المأخوذة ---
                $taken = LeaveRequest::where('user_id', $user->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'approved')
                    ->whereYear('start_date', date('Y'))
                    ->get()
                    ->sum(function ($request) use ($leaveType) {
                        if ($leaveType->unit === 'days') {
                            return Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
                        } else { 
                            return ($request->start_time && $request->end_time) ? (strtotime($request->end_time) - strtotime($request->start_time)) / 3600 : 0;
                        }
                    });
                
                $userBalances[$leaveType->id] = [
                    'balance' => $user->getLeaveBalance($leaveType),
                    'unit' => $leaveType->unit,
                    'taken' => $taken,
                    'show_taken' => $leaveType->show_taken_in_report,
                ];
            }
            $balanceData[$user->id] = [
                'name' => $user->name,
                'balances' => $userBalances,
            ];
        }

        return view('admin.balances.index', compact('balanceData', 'leaveTypes'));
    }
}