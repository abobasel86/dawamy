<div>
    {{-- Filter Form --}}
    <div class="bg-white shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة تقرير الإجازات</h3>
            <form method="GET" action="{{ route('admin.reports.index') }}">
                <input type="hidden" name="tab" value="leaves">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="leave_user_ids" class="block font-medium text-sm text-gray-700">الموظف</label>
                        <select name="leave_user_ids[]" id="leave_user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, request('leave_user_ids', [])))>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="leave_department_id" class="block font-medium text-sm text-gray-700">القسم</label>
                        <select name="leave_department_id" id="leave_department_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            <option value="">كل الأقسام</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(request('leave_department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="leave_location_id" class="block font-medium text-sm text-gray-700">الموقع</label>
                        <select name="leave_location_id" id="leave_location_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                             <option value="">كل المواقع</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" @selected(request('leave_location_id') == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="leave_start_date" class="block font-medium text-sm text-gray-700">من تاريخ</label>
                            <input type="date" name="leave_start_date" id="leave_start_date" value="{{ request('leave_start_date') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>
                        <div>
                            <label for="leave_end_date" class="block font-medium text-sm text-gray-700">إلى تاريخ</label>
                            <input type="date" name="leave_end_date" id="leave_end_date" value="{{ request('leave_end_date') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end mt-4 px-6 pb-6">
                    <a href="{{ route('admin.reports.index', ['tab' => 'leaves']) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                    <button type="submit" class="btn-primary">عرض التقرير</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">نتائج التقرير</h3>
                @if(request()->hasAny(['leave_user_ids', 'leave_department_id', 'leave_location_id', 'leave_start_date', 'leave_end_date']))
                    <a href="{{ route('admin.reports.export.leaves', request()->query()) }}" class="btn-secondary">تصدير النتائج الحالية</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">القسم</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع الإجازة</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">السبب</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التقديم</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">تفاصيل المدة</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدة</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة النهائية</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">تتبع الطلب</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($leaves as $leave)
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $leave->user->name ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ optional($leave->user->department)->name ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $leave->reason }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $leave->created_at->format('Y-m-d') }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">
                                    @if ($leave->leaveType->unit === 'days')
                                        {{ $leave->start_date->format('Y-m-d') }}<br>إلى {{ $leave->end_date->format('Y-m-d') }}
                                    @else
                                        {{ $leave->start_date->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($leave->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($leave->end_time)->format('h:i A') }}
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $leave->getDurationForHumans() }}</td>
                                <td class="px-3 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $leave->status == 'approved' ? 'green' : ($leave->status == 'rejected' ? 'red' : 'yellow') }}-100 text-{{ $leave->status == 'approved' ? 'green' : ($leave->status == 'rejected' ? 'red' : 'yellow') }}-800">{{ $leave->status }}</span></td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">
                                    @php $statusDetails = $leave->getRequestStatusDetails(); @endphp
                                    <span class="{{ $statusDetails['class'] }}">{{ $statusDetails['text'] }}</span>
                                    @if ($leave->status == 'rejected' && $leave->rejection_reason)
                                        <div class="text-xs text-gray-500 mt-1"><strong>سبب الرفض:</strong> {{ $leave->rejection_reason }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-6 py-10 text-center text-gray-500">لا توجد بيانات تطابق الفلترة الحالية.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             <div class="mt-4">{{ $leaves->withQueryString()->links() }}</div>
        </div>
    </div>
</div>