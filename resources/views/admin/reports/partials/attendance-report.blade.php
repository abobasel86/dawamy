{{-- Filter Form --}}
<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">فلترة التقرير</h3>
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <input type="hidden" name="tab" value="attendance">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="user_ids" class="block font-medium text-sm text-gray-700 dark:text-gray-300">الموظف</label>
                    <select name="user_ids[]" id="user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, request('user_ids', [])))>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">من تاريخ</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                </div>
                <div>
                    <label for="end_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">إلى تاريخ</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                </div>
            </div>
            <div class="flex items-center justify-end mt-6">
                <a href="{{ route('admin.reports.index', ['tab' => 'balances']) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                <button type="submit" class="btn-primary">عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

{{-- Report Results --}}
@if($logs->isNotEmpty())
<div class="bg-white shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200 overflow-x-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">نتائج التقرير</h3>

            @if(request()->has('user_ids') || request()->has('start_date') || request()->has('end_date'))
                <a href="{{ route('admin.reports.export.attendance', request()->query()) }}"
                   class="btn-secondary">
                    تصدير النتائج الحالية
                </a>
            @endif
        </div>


        {{-- جدول النتائج --}}
        <div class="overflow-x-auto" x-data="{ openDetails: {} }">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">وقت الحضور</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">وقت الانصراف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التفاصيل</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php
                            $overtimes = $log->overtimes ?? collect();
                            $hasDetails = ($log->lateness_minutes > 0 && $log->justification) || $overtimes->isNotEmpty();
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $log->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->punch_in_time->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->punch_in_time->format('h:i A') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($log->punch_out_time)
                                    {{ $log->punch_out_time->format('h:i A') }}
                                @else
                                    <span class="text-red-500">لم يسجل انصراف</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($hasDetails)
                                    <button @click="openDetails[{{ $log->id }}] = !openDetails[{{ $log->id }}]"
                                            class="text-blue-600 hover:text-blue-900 text-xs">
                                        (عرض)
                                    </button>
                                @else
                                    ---
                                @endif
                            </td>
                        </tr>

                        {{-- تفاصيل السجل --}}
                        <tr x-show="openDetails[{{ $log->id }}]" x-cloak>
                            <td colspan="6" class="p-4 bg-gray-100 dark:bg-gray-900">
                                <div class="space-y-4">
                                    @if($log->lateness_minutes > 0 && $log->justification)
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200">مبرر التأخير ({{ $log->lateness_minutes }} دقيقة):</h4>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $log->justification }}</p>
                                        </div>
                                    @endif

                                    @if($overtimes->isNotEmpty())
                                        @foreach($overtimes as $overtime)
                                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2">
                                                <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200">
                                                    تفاصيل العمل الإضافي ({{ $overtime->start_time->format('h:i A') }} - {{ $overtime->end_time ? $overtime->end_time->format('h:i A') : '...' }})
                                                </h4>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>السبب:</strong> {{ $overtime->reason }}</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>الحالة:</strong> {{ __($overtime->status) }}</p>

                                                <h5 class="font-semibold text-xs mt-2 text-gray-700 dark:text-gray-300">سجل الموافقات:</h5>
                                                <ul class="list-disc list-inside mt-1 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                                    @forelse($overtime->approvalHistory as $history)
                                                        <li>
                                                            <strong>{{ $history->approver->name }}</strong>:
                                                            <span class="font-mono p-1 rounded text-xs
                                                                @if($history->status == 'rejected') bg-red-100 text-red-800 @else bg-blue-100 text-blue-800 @endif">
                                                                {{ $history->status }}
                                                            </span>
                                                            @if($history->remarks)
                                                                <span class="italic">- {{ $history->remarks }}</span>
                                                            @endif
                                                        </li>
                                                    @empty
                                                        <li>لم يتم اتخاذ أي إجراء على هذا الطلب بعد.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">لا توجد بيانات لعرضها.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ✅ روابط الصفحات --}}
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endif
