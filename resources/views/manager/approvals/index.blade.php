<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('موافقات الإجازات') }}
        </h2>
    </x-slot>

    <div x-data="{ rejectionModalOpen: false, currentApprovalId: null, formAction: '' }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">طلبات الإجازة المعلقة</h3>

                        @if (session('success'))
                            <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>
                        @endif
                         @if ($errors->any())
                            <div class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md">
                               <p class="font-bold">يرجى تصحيح الأخطاء التالية:</p>
                               <ul class="list-disc list-inside mt-2">
                                   @foreach ($errors->all() as $error)
                                       <li>{{ $error }}</li>
                                   @endforeach
                               </ul>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        {{-- Table headers remain the same --}}
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
                                            $leaveRequest = $approval->leaveRequest;
                                            if (!$leaveRequest || !$leaveRequest->user || !$leaveRequest->leaveType) { continue; }
                                        @endphp
                                        <tr>
                                            {{-- Table data cells remain the same --}}
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leaveType->name }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $leaveRequest->reason }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->created_at->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                                @if($leaveRequest->leaveType->unit === 'days')
                                                    <div><strong>من:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') }}</div>
                                                    <div><strong>إلى:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d') }}</div>
                                                @else
                                                    <div><strong>في تاريخ:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') }}</div>
                                                    <div><strong>من:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_time)->format('h:i A') }}</div>
                                                    <div><strong>إلى:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_time)->format('h:i A') }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-bold">{{ $leaveRequest->employee_balance }} {{ $leaveRequest->leaveType->unit === 'days' ? 'يوم' : 'ساعة' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @forelse($leaveRequest->attachments as $attachment)
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 hover:underline block text-sm">عرض المرفق</a>
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
                                               
                                                    
                                                    {{-- Reject Button (triggers modal) --}}
                                                    <button @click="rejectionModalOpen = true; currentApprovalId = {{ $approval->id }}; formAction = '{{ route('manager.approvals.update', $approval) }}'" 
                                                            type="button" 
                                                            class="px-3 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded-md">
                                                        رفض
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-4 text-center">لا توجد طلبات معلقة حالياً.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="rejectionModalOpen" @keydown.escape.window="rejectionModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="rejectionModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="rejectionModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="rejectionModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-right align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:align-middle">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">سبب رفض الإجازة</h3>
                    <form :action="formAction" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <div>
                            <label for="rejection_reason" class="sr-only">سبب الرفض</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="block w-full border-gray-300 rounded-md shadow-sm" placeholder="يرجى كتابة سبب واضح لرفض الطلب..." required minlength="5"></textarea>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:col-start-2 sm:text-sm">تأكيد الرفض</button>
                            <button @click="rejectionModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>