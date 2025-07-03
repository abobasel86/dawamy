<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserOvertimeRequestController extends Controller
{
    /**
     * عرض جميع طلبات العمل الإضافي الخاصة بالموظف الحالي
     */
    public function index()
    {
        $user = Auth::user();

        $myRequests = OvertimeRequest::where('user_id', $user->id)
                                     ->with(['currentApprover', 'approvalHistory.approver'])
                                     ->latest()
                                     ->paginate(15);

        return view('overtime.my_requests', compact('myRequests'));
    }
}
