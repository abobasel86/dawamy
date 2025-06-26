<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('التقارير') }}
        </h2>
    </x-slot>

    @php
        $activeTab = request('tab', 'attendance');
    @endphp

    <div class="py-12" x-data="{ tab: '{{ $activeTab }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border-b mb-6">
                <nav class="flex space-x-4 rtl:space-x-reverse">
                    <button type="button" @click="tab='attendance'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-bold': tab==='attendance'}" class="px-4 py-2">تقرير الحضور والانصراف</button>
                    <button type="button" @click="tab='balances'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-bold': tab==='balances'}" class="px-4 py-2">أرصدة الإجازات</button>
                    <button type="button" @click="tab='employees'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-bold': tab==='employees'}" class="px-4 py-2">الموظفين</button>
                </nav>
            </div>

            <!-- Attendance Tab -->
            <div x-show="tab==='attendance'">
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة التقرير</h3>
                        <form method="GET" action="{{ route('admin.reports.index') }}">
                            <input type="hidden" name="tab" value="attendance">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="user_ids" class="block font-medium text-sm text-gray-700">الموظف</label>
                                    <select name="user_ids[]" id="user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(in_array($user->id, request('user_ids', [])))>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">يمكنك اختيار موظف أو أكثر. اتركه فارغاً لعرض الكل.</p>
                                </div>
                                <div>
                                    <label for="start_date" class="block font-medium text-sm text-gray-700">من تاريخ</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="end_date" class="block font-medium text-sm text-gray-700">إلى تاريخ</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.reports.index', ['tab' => 'attendance']) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                                <button type="submit" class="btn-primary">عرض التقرير</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">نتائج التقرير</h3>
                            @if(request()->has('start_date'))
                                <a href="{{ route('admin.reports.export.attendance', request()->query()) }}" class="btn-secondary">تصدير النتائج الحالية</a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">الحضور</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">صورة الحضور</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">الانصراف</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">صورة الانصراف</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">مدة العمل</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">{{ $log->user->name ?? 'غير معروف' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->punch_in_time)->format('Y-m-d') }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">{{ \Carbon\Carbon::parse($log->punch_in_time)->format('h:i A') }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                @if($log->punch_in_selfie_path)
                                                    <button type="button" onclick="showImageModal('{{ asset('storage/' . $log->punch_in_selfie_path) }}')" class="text-indigo-600 hover:text-indigo-900 hover:underline text-sm font-semibold">عرض الصورة</button>
                                                @else
                                                    <span class="text-gray-400">لا يوجد</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                @if($log->punch_out_time)
                                                    {{ \Carbon\Carbon::parse($log->punch_out_time)->format('h:i A') }}
                                                @else
                                                    <span class="text-gray-500">لم يسجل انصراف</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                @if($log->punch_out_selfie_path)
                                                    <button type="button" onclick="showImageModal('{{ asset('storage/' . $log->punch_out_selfie_path) }}')" class="text-indigo-600 hover:text-indigo-900 hover:underline text-sm font-semibold">عرض الصورة</button>
                                                @else
                                                    <span class="text-gray-400">لا يوجد</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                @if($log->punch_out_time)
                                                    @php
                                                        $punchIn = \Carbon\Carbon::parse($log->punch_in_time);
                                                        $punchOut = \Carbon\Carbon::parse($log->punch_out_time);
                                                        echo $punchIn->diff($punchOut)->format('%h س و %i د');
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                                @if(request()->has('start_date'))
                                                    لا توجد نتائج تطابق معايير البحث.
                                                @else
                                                    الرجاء تحديد الفلاتر لعرض التقرير.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balances Tab -->
            <div x-show="tab==='balances'" x-cloak>
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة التقرير</h3>
                        <form method="GET" action="{{ route('admin.reports.index') }}">
                            <input type="hidden" name="tab" value="balances">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="balance_department_id" class="block font-medium text-sm text-gray-700">القسم</label>
                                    <select name="balance_department_id" id="balance_department_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">كل الأقسام</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @selected(request('balance_department_id') == $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="balance_user_ids" class="block font-medium text-sm text-gray-700">الموظف</label>
                                    <select name="balance_user_ids[]" id="balance_user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(in_array($user->id, request('balance_user_ids', [])))>{{ $user->name }}</option>
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
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">نتائج التقرير</h3>
                            @if(request()->has('balance_department_id') || request()->has('balance_user_ids'))
                                <a href="{{ route('admin.reports.export.balances', request()->query()) }}" class="btn-secondary">تصدير النتائج الحالية</a>
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

            <!-- Employees Tab -->
            <div x-show="tab==='employees'" x-cloak>
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة البحث</h3>
                        <form method="GET" action="{{ route('admin.reports.index') }}">
                            <input type="hidden" name="tab" value="employees">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="emp_name" class="block text-sm font-medium text-gray-700">الاسم</label>
                                    <input type="text" name="emp_name" id="emp_name" value="{{ request('emp_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label for="emp_department_id" class="block text-sm font-medium text-gray-700">القسم</label>
                                    <select name="emp_department_id" id="emp_department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">كل الأقسام</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @selected(request('emp_department_id') == $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="emp_location_id" class="block text-sm font-medium text-gray-700">الموقع</label>
                                    <select name="emp_location_id" id="emp_location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">كل المواقع</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" @selected(request('emp_location_id') == $location->id)>{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.reports.index', ['tab' => 'employees']) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                                <button type="submit" class="btn-primary">بحث</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">القسم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموقع</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التعيين</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التثبيت</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($employees as $emp)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $emp->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $emp->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $emp->department->name ?? '' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $emp->location->name ?? '' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($emp->hire_date)->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($emp->permanent_date)->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">لا يوجد بيانات لعرضها.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden p-4" onclick="closeImageModal()">
        <div class="bg-white p-4 rounded-lg shadow-xl relative max-w-4xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-0 right-0 -m-3 text-white bg-gray-800 border-2 border-white rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold z-10 hover:bg-red-600 transition-colors">&times;</button>
            <div class="overflow-hidden rounded-md flex justify-center items-center min-h-[300px]">
                <img id="modalImage" src="" alt="صورة الموظف" style="max-width: 100%; max-height: 80vh; object-fit: contain; display: block;">
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showImageModal(src) {
                document.getElementById('modalImage').src = src;
                document.getElementById('imageModal').classList.remove('hidden');
            }
            function closeImageModal() {
                document.getElementById('imageModal').classList.add('hidden');
            }
            document.getElementById('imageModal').onclick = closeImageModal;
        </script>
    @endpush
</x-app-layout>
