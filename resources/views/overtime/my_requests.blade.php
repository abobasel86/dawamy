<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('طلباتي للعمل الإضافي') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الوقت</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">المدة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الطلب عند</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-data="{ openDetails: {} }">
                                @forelse($myRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_time->format('h:i A') }} - {{ $request->end_time ? $request->end_time->format('h:i A') : '...' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->actual_minutes }} دقيقة</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($request->status == 'approved') bg-green-100 text-green-800
                                                @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ __($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->currentApprover->name ?? '---' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click="openDetails[{{ $request->id }}] = !openDetails[{{ $request->id }}]" class="text-blue-500 hover:text-blue-700 text-xs">(عرض)</button>
                                        </td>
                                    </tr>
                                    <tr x-show="openDetails[{{ $request->id }}]" x-cloak>
                                        <td colspan="6" class="p-4 bg-gray-50 dark:bg-gray-900">
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
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">لا توجد لديك طلبات عمل إضافي.</td>
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
