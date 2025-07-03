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
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">نتائج البحث</h3>
                            @if(request()->has('emp_name') || request()->has('emp_department_id') || request()->has('emp_location_id'))
                                <a href="{{ route('admin.reports.export.employees', request()->query()) }}" class="btn-secondary">تصدير النتائج الحالية</a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
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
                    </div>
                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>