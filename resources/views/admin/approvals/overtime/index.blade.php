<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('موافقات العمل الإضافي') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, actionUrl: '', decision: '', remarks: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الموظف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">وقت الحضور</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">وقت الانصراف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">المدة (دقيقة)</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">السبب</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-data="{ openDetails: {} }">
                                @forelse($pendingRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $request->start_time->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $request->start_time->format('h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($request->end_time)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $request->end_time->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    لم يتم تسجيل الانصراف
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->actual_minutes }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $request->reason }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($request->end_time)
                                                <button @click="showModal = true; actionUrl = '{{ route('admin.overtime.approvals.process', $request) }}'; decision = 'approve'; remarks = ''" class="text-green-600 hover:text-green-900">موافقة</button>
                                                <button @click="showModal = true; actionUrl = '{{ route('admin.overtime.approvals.process', $request) }}'; decision = 'reject'; remarks = ''" class="text-red-600 hover:text-red-900 ms-2">رفض</button>
                                            @endif
                                            <button @click="openDetails[{{ $request->id }}] = !openDetails[{{ $request->id }}]" class="text-blue-500 hover:text-blue-700 ms-2 text-xs">(التفاصيل)</button>
                                        </td>
                                    </tr>
                                    {{-- صف تفاصيل سجل التتبع --}}
                                    <tr x-show="openDetails[{{ $request->id }}]" x-cloak>
                                        <td colspan="7" class="p-4 bg-gray-50 dark:bg-gray-900">
                                            <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300">سجل تتبع الطلب:</h4>
                                            <ul class="list-disc list-inside mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                @forelse($request->approvalHistory as $history)
                                                    <li>
                                                        <strong>{{ $history->approver->name }}</strong>:
                                                        <span class="font-mono p-1 rounded text-xs
                                                            @if($history->status == 'rejected') bg-red-100 text-red-800 @else bg-blue-100 text-blue-800 @endif">
                                                            {{ $history->status }}
                                                        </span>
                                                        <span class="text-gray-400">في {{ $history->created_at->format('Y-m-d h:i A') }}</span>
                                                        @if($history->remarks)
                                                            <p class="pl-4 mt-1"><em>الملاحظات: {{ $history->remarks }}</em></p>
                                                        @endif
                                                    </li>
                                                @empty
                                                    <li>لم يتم اتخاذ أي إجراء على هذا الطلب بعد.</li>
                                                @endforelse
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">لا توجد طلبات معلقة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal (Version 4 - Final UI Fix) -->
        <div x-show="showModal" x-cloak 
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <!-- **الإصلاح هنا: إزالة w-full وترك max-w-md للتحكم بالحجم** -->
            <div @click.away="showModal = false" class="relative mx-auto p-5 border max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <form :action="actionUrl" method="POST" class="w-full">
                    @csrf
                    <input type="hidden" name="decision" :value="decision">

                    <div class="mt-3 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" x-text="decision === 'approve' ? 'تأكيد الموافقة' : 'سبب الرفض'"></h3>
                    </div>

                    <div class="mt-4 text-right px-4">
                        <p x-show="decision === 'approve'" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            هل أنت متأكد من موافقتك على هذا الطلب؟
                        </p>
                        
                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-text="decision === 'reject' ? 'سبب الرفض (إلزامي)' : 'ملاحظات (اختياري)'"></span>
                            </label>
                            <textarea name="remarks" id="remarks" x-model="remarks" rows="3" 
                                      class="mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                      :required="decision === 'reject'"></textarea>
                        </div>
                    </div>

                    <!-- **الإصلاح هنا: استخدام flexbox لترتيب الأزرار بشكل موثوق** -->
                    <div class="mt-5 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:gap-3 gap-2">
    <!-- زر تأكيد الإجراء -->
    <button type="submit"
        class="w-full sm:w-auto inline-flex items-center justify-center rounded-full px-6 py-2.5 text-base font-semibold text-white transition-transform duration-300 hover:scale-105 shadow-md"
        :style="decision === 'approve' 
            ? 'background-color: #156b68;' 
            : 'background-color: #caa453;'">
        تأكيد الإجراء
    </button>

    <!-- زر إلغاء -->
    <button type="button" @click="showModal = false"
        class="w-full sm:w-auto inline-flex items-center justify-center rounded-full px-6 py-2.5 text-base font-semibold border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600 transition">
        إلغاء
    </button>
</div>



                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
