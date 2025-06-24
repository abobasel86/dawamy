<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('موافقات الإجازات') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">طلبات الإجازة المعلقة</h3>

                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع الإجازة</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">السبب</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التقديم</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تفاصيل الإجازة</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد المتبقي</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المرفقات</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($pendingApprovals as $approval)
            @php
                // للتعامل مع الحالات التي قد يكون فيها الطلب أو نوع الإجازة محذوفاً
                $leaveRequest = $approval->leaveRequest;
                if (!$leaveRequest || !$leaveRequest->user || !$leaveRequest->leaveType) {
                    continue;
                }
            @endphp
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->user->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leaveType->name }}</td>
				<td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $leaveRequest->reason }}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800">
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $leaveRequest->created_at->format('Y-m-d') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                    @if($leaveRequest->leaveType->unit === 'days')
                        <div><strong>من:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') }}</div>
                        <div><strong>إلى:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d') }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            (المدة: {{ \Carbon\Carbon::parse($leaveRequest->start_date)->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1 }} أيام)
                        </div>
                    @else
                        <div><strong>في تاريخ:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') }}</div>
                        <div><strong>من:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_time)->format('h:i A') }}</div>
                        <div><strong>إلى:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_time)->format('h:i A') }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-bold">
                    {{ $leaveRequest->employee_balance }} {{ $leaveRequest->leaveType->unit === 'days' ? 'يوم' : 'ساعة' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @forelse($leaveRequest->attachments as $attachment)
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 hover:underline block text-sm">
                            عرض المرفق
                        </a>
                    @empty
                        <span class="text-gray-500">لا يوجد</span>
                    @endforelse
                </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div style="display: flex; gap: 8px;">
                                                <form method="POST" action="{{ route('manager.approvals.update', $approval) }}" onsubmit="return confirm('هل أنت متأكد من الموافقة على هذا الطلب؟');">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" style="background-color: #10B981; color: white; padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer;">موافقة</button>
                                                </form>
                                                <form method="POST" action="{{ route('manager.approvals.update', $approval) }}" onsubmit="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" style="background-color: #EF4444; color: white; padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer;">رفض</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center">لا توجد طلبات معلقة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
