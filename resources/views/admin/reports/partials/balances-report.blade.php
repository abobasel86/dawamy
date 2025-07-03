<div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة التقرير</h3>
                        <form method="GET" action="{{ route('admin.reports.index') }}">
                            <input type="hidden" name="tab" value="balances">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                
                                <div>
                                    <label for="balance_user_ids" class="block font-medium text-sm text-gray-700">الموظف</label>
                                    <select name="balance_user_ids[]" id="balance_user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(in_array($user->id, request('balance_user_ids', [])))>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="balance_department_id" class="block font-medium text-sm text-gray-700">القسم</label>
                                    <select name="balance_department_id" id="balance_department_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">كل الأقسام</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @selected(request('balance_department_id') == $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.reports.index', ['tab' => 'balances']) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                                <button type="submit" class="btn-primary">عرض التقرير</button>
                            </div>
                        </form>
                    </div>
                </div>
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
                        <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th rowspan="2" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase align-middle">اسم الموظف</th>
                                    @foreach ($leaveTypes as $leaveType)
                                        <th colspan="{{ $leaveType->show_taken_in_report ? 2 : 1 }}" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-b border-l">{{ $leaveType->name }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($leaveTypes as $leaveType)
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l">الرصيد المتبقي</th>
                                        @if($leaveType->show_taken_in_report)
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l">المأخوذ هذا العام</th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($balanceData as $data)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $data['name'] }}</td>
                                        @foreach ($leaveTypes as $leaveType)
                                            <td class="px-6 py-4 whitespace-nowrap text-center border-l">
                                                {{ $data['balances'][$leaveType->id]['balance'] }}
                                                <span class="text-xs text-gray-500">{{ $data['balances'][$leaveType->id]['unit'] === 'days' ? 'يوم' : 'ساعة' }}</span>
                                            </td>
                                            @if($leaveType->show_taken_in_report)
                                                <td class="px-6 py-4 whitespace-nowrap text-center border-l bg-gray-50">
                                                    {{ $data['balances'][$leaveType->id]['taken'] }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($leaveTypes) * 2 + 1 }}" class="px-6 py-4 text-center">لا يوجد بيانات لعرضها.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>