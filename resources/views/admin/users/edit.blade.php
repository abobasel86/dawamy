<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('تعديل المستخدم: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Display success & error messages --}}
            @if (session('success'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md">
                   <ul class="list-disc list-inside">
                       @foreach ($errors->all() as $error)
                           <li>{{ $error }}</li>
                       @endforeach
                   </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">البيانات الأساسية والوظيفية</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">الاسم</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                            </div>

                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">البريد الإلكتروني</label>
                                <input type="email" id="email" value="{{ $user->email }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 bg-gray-100" disabled>
                            </div>
                            
                            <div>
                                <label for="is_active" class="block font-medium text-sm text-gray-700">حالة الحساب</label>
                                <select name="is_active" id="is_active" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="1" @selected($user->is_active)>فعال</option>
                                    <option value="0" @selected(!$user->is_active)>غير فعال</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="employment_status" class="block font-medium text-sm text-gray-700">حالة الموظف</label>
                                <select name="employment_status" id="employment_status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="probation" @selected($user->employment_status == 'probation')>تحت الاختبار</option>
                                    <option value="permanent" @selected($user->employment_status == 'permanent')>خدمات</option>
                                    <option value="contract" @selected($user->employment_status == 'contract')>عقد</option>
                                </select>
                            </div>

                            <div>
                                <label for="hire_date" class="block font-medium text-sm text-gray-700">تاريخ بدء العمل</label>
                                <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date', optional($user->hire_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            </div>

                            <div>
                                <label for="probation_end_date" class="block font-medium text-sm text-gray-700">تاريخ نهاية فترة الاختبار</label>
                                <input type="date" name="probation_end_date" id="probation_end_date" value="{{ old('probation_end_date', optional($user->probation_end_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            </div>
                            
                            <div>
                                <label for="permanent_date" class="block font-medium text-sm text-gray-700">تاريخ التثبيت</label>
                                <input type="date" name="permanent_date" id="permanent_date" value="{{ old('permanent_date', optional($user->permanent_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            </div>

                            <div>
                                <label for="role" class="block font-medium text-sm text-gray-700">الدور في النظام</label>
                                <select name="role" id="role" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="department_id" class="block font-medium text-sm text-gray-700">القسم</label>
                                <select name="department_id" id="department_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="">-- لا ينتمي لقسم --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected($user->department_id == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="manager_id" class="block font-medium text-sm text-gray-700">المدير المباشر</label>
                                <select name="manager_id" id="manager_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="">-- يتبع لمدير القسم --</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ $user->manager_id == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="location_id" class="block font-medium text-sm text-gray-700">موقع العمل</label>
                                <select name="location_id" id="location_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <option value="">-- اختر موقع العمل --</option>
                                    @if(isset($locations))
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" @selected(old('location_id', $user->location_id) == $location->id)>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- المستندات الرسمية -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">المستندات الرسمية</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($documentTypes as $docType)
                                <div>
                                    <label for="doc_{{ $docType->id }}" class="block font-medium text-sm text-gray-700">{{ $docType->name }}</label>
                                    <input type="file" name="documents[{{ $docType->id }}]" id="doc_{{ $docType->id }}" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 mt-1">
                                    @if(isset($userDocuments[$docType->id]))
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $userDocuments[$docType->id]->file_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                                عرض {{ $userDocuments[$docType->id]->documentType->name }} الحالي
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500 md:col-span-2">لم يتم تعريف أي أنواع للمستندات بعد. يرجى إضافتها من صفحة "أنواع المستندات".</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- تحديد أرصدة الإجازات -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">تحديد أرصدة الإجازات المخصصة</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($leaveTypes as $type)
                                <div>
                                    <label for="balance_{{ $type->id }}" class="block font-medium text-sm text-gray-700">{{ $type->name }}</label>
                                    <input type="number" name="balances[{{ $type->id }}][balance]" id="balance_{{ $type->id }}" 
                                           value="{{ old('balances.'.$type->id.'.balance', $userBalances[$type->id] ?? $type->days_annually) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="btn-primary">
                        تحديث البيانات
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
