@php
    use Carbon\Carbon;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('فريق العمل') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- قسم الفلاتر (يظهر للأمين العام فقط) --}}
            @if(Auth::user()->hasRole(['secretary_general', 'admin']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة البحث</h3>
                        <form method="GET" action="{{ route('manager.team.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="employee_name" class="block text-sm font-medium text-gray-700">اسم الموظف</label>
                                    <input type="text" name="employee_name" id="employee_name" value="{{ request('employee_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label for="department_id" class="block text-sm font-medium text-gray-700">القسم</label>
                                    <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">كل الأقسام</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="btn-primary">بحث</button>
                                    <a href="{{ route('manager.team.index') }}" class="mr-3 text-sm text-gray-600 hover:text-gray-900">إعادة تعيين</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- جدول عرض الموظفين --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
        <tr>
            <th rowspan="2" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase align-middle">الموظف</th>
            {{-- ===== العمود الجديد ===== --}}
            <th rowspan="2" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase align-middle">حالة اليوم</th>
            <th rowspan="2" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase align-middle">المستندات الرسمية</th>
            @foreach ($leaveTypes as $leaveType)
                <th colspan="{{ $leaveType->show_taken_in_report ? 2 : 1 }}" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase border-b border-l">{{ $leaveType->name }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($leaveTypes as $leaveType)
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border-l">المتبقي</th>
                @if($leaveType->show_taken_in_report)
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border-l bg-gray-100">المأخوذ</th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($employees as $employee)
            <tr>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                    <div class="text-sm text-gray-500">{{ $employee->department->name ?? '' }}</div>
                </td>
                {{-- ===== عرض بيانات العمود الجديد ===== --}}
                <td class="px-4 py-4 whitespace-nowrap">
                    @php $status = $employee->getCurrentStatus(); @endphp
                    <div class="{{ $status['class'] }}">{{ $status['status'] }}</div>
                    @if($status['time'])
                        <div class="text-xs text-gray-500">
                            {{ $status['time'] }}
                        </div>
                    @endif
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                    @forelse($employee->documents as $doc)
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-blue-600 hover:underline block">{{ $doc->documentType->name }}</a>
                    @empty
                        <span class="text-gray-400">لا يوجد</span>
                    @endforelse
                </td>
                @foreach ($leaveTypes as $leaveType)
                                            <td class="px-4 py-4 whitespace-nowrap text-center border-l">
                                                {{ $employee->getLeaveBalance($leaveType) }}
                                            </td>
                                            @if($leaveType->show_taken_in_report)
                                                <td class="px-4 py-4 whitespace-nowrap text-center border-l bg-gray-50">
                                                    {{ $employee->leaveRequests->where('leave_type_id', $leaveType->id)->sum(function($lr) use ($leaveType) {
                                                        if ($leaveType->unit === 'days') {
                                                            return Carbon::parse($lr->start_date)->diffInDays(Carbon::parse($lr->end_date)) + 1;
                                                        } else {
                                                            return ($lr->start_time && $lr->end_time) ? (strtotime($lr->end_time) - strtotime($lr->start_time)) / 3600 : 0;
                                                        }
                                                    }) }}
                                                </td>
                                            @endif
                                        @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ 3 + $leaveTypes->count() + $leaveTypes->where('show_taken_in_report', true)->count() }}" class="px-6 py-10 text-center text-gray-500">
                    لا يوجد موظفون لعرضهم.
                </td>
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
</x-app-layout>