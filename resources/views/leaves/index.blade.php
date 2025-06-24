<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('إدارة الإجازات') }}
        </h2>
    </x-slot>

    <!-- Alpine.js component for the dynamic form -->
    <div class="py-12" x-data="leaveForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- (Display Leave Balances - remains the same) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-baseline">
                         <h3 class="text-lg font-medium text-gray-900">رصيد اجازاتك:</h3>
                         <p class="text-3xl font-bold rtl:mr-3 ltr:ml-3" style="color: var(--primary-color);">{{ $annualLeaveBalance }}</p>
                         <p class="text-lg text-gray-600 rtl:mr-1 ltr:ml-1">يوم</p>
                    </div>
                </div>
            </div>

            <!-- New Leave Request Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">تقديم طلب إجازة جديد</h3>

                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 font-medium text-sm text-red-700 bg-red-100 p-3 rounded-md">{{ session('error') }}</div>
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

                    <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Leave Type -->
                            <div>
                                <label for="leave_type_id" class="block font-medium text-sm text-gray-700">نوع الإجازة</label>
                                <select name="leave_type_id" id="leave_type_id" x-model="selectedType" @change="updateFormVisibility" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                    <option value="">اختر نوع الإجازة</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <!-- Reason -->
                             <div>
                                <label for="reason" class="block font-medium text-sm text-gray-700">السبب</label>
                                <input type="text" name="reason" id="reason" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                            </div>

                             <!-- Delegated User Field -->
                            <div class="md:col-span-2" x-show="requiresDelegation" x-transition>
                                <label for="delegated_user_id" class="block font-medium text-sm text-gray-700">الموظف المفوّض (من سيقوم بمهامك)</label>
                                <select name="delegated_user_id" id="delegated_user_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" :required="requiresDelegation">
                                    <option value="">-- اختر زميلاً --</option>
                                    @foreach($colleagues as $colleague)
                                        <option value="{{ $colleague->id }}">{{ $colleague->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daily Leave Fields -->
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-show="isDaily" x-transition>
                                <div>
                                    <label for="start_date_daily" class="block font-medium text-sm text-gray-700">تاريخ البدء</label>
                                    <input type="date" name="start_date" id="start_date_daily" :disabled="!isDaily" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="end_date_daily" class="block font-medium text-sm text-gray-700">تاريخ الانتهاء</label>
                                    <input type="date" name="end_date" id="end_date_daily" :disabled="!isDaily" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>

                            <!-- Hourly Leave Fields -->
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6" x-show="isHourly" x-transition>
                                <div>
                                    <label for="date_hourly" class="block font-medium text-sm text-gray-700">التاريخ</label>
                                    <input type="date" name="start_date_hourly" id="date_hourly" :disabled="!isHourly" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="start_time" class="block font-medium text-sm text-gray-700">من الساعة</label>
                                    <input type="time" name="start_time" id="start_time" :disabled="!isHourly" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="end_time" class="block font-medium text-sm text-gray-700">إلى الساعة</label>
                                    <input type="time" name="end_time" id="end_time" :disabled="!isHourly" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                            
                            <!-- Attachment Field -->
                            <div class="md:col-span-2" x-show="requiresAttachment" x-transition>
                                <label class="block font-medium text-sm text-gray-700">إرفاق ملفات</label>
                                <template x-for="(field, index) in attachmentFields" :key="field.id">
                                    <div class="flex items-center mt-2">
                                        <input type="file" name="attachments[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                                        <button type="button" @click="removeAttachmentField(field.id)" x-show="index > 0" class="ml-2 rtl:mr-2 text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addAttachmentField()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-semibold">+ إضافة ملف آخر</button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="btn-primary">
                                إرسال الطلب
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leave Requests History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">سجل طلبات الإجازة</h3>
                     <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع الإجازة</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">السبب</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التقديم</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">تفاصيل المدة</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">المدة</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة النهائية</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">تتبع الطلب</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($leaveRequests as $request)
    <tr>
        {{-- 1. نوع الإجازة --}}
        <td class="px-4 py-4 whitespace-nowrap">{{ $request->leaveType->name }}</td>
        
        {{-- 2. السبب --}}
        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $request->reason }}</td>
        
        {{-- 3. تاريخ التقديم --}}
        <td class="px-4 py-4 whitespace-nowrap">{{ $request->created_at->format('Y-m-d') }}</td>
        
        {{-- 4. تفاصيل المدة --}}
        <td class="px-4 py-4 whitespace-nowrap text-sm">
            @if ($request->leaveType->unit === 'days')
                <div>من: {{ \Carbon\Carbon::parse($request->start_date)->format('Y-m-d') }}</div>
                <div>إلى: {{ \Carbon\Carbon::parse($request->end_date)->format('Y-m-d') }}</div>
            @else
                <div>{{ \Carbon\Carbon::parse($request->start_date)->format('Y-m-d') }}</div>
                <div class="text-xs">
                    من {{ \Carbon\Carbon::parse($request->start_time)->format('h:i A') }} إلى {{ \Carbon\Carbon::parse($request->end_time)->format('h:i A') }}
                </div>
            @endif
        </td>
        
        {{-- 5. المدة --}}
        <td class="px-4 py-4 whitespace-nowrap text-center">
            {{ $request->getDurationForHumans() }}
        </td>
        
        {{-- 6. الحالة النهائية --}}
        <td class="px-4 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                @if($request->status == 'approved') bg-green-100 text-green-800 @endif
                @if($request->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                @if($request->status == 'rejected') bg-red-100 text-red-800 @endif
                @if($request->status == 'cancelled') bg-gray-100 text-gray-800 @endif">
                @if($request->status == 'approved') موافق عليه @endif
                @if($request->status == 'pending') قيد المراجعة @endif
                @if($request->status == 'rejected') مرفوض @endif
                @if($request->status == 'cancelled') ملغي @endif
            </span>
        </td>
        
        {{-- 7. تتبع الطلب --}}
        <td class="px-4 py-4 whitespace-nowrap text-sm">
            @php $statusDetails = $request->getRequestStatusDetails(); @endphp
            <span class="{{ $statusDetails['class'] }}">
                {{ $statusDetails['text'] }}
            </span>
        </td>
    </tr>
@empty
    {{-- تم تصحيح colspan ليطابق عدد الأعمدة الصحيح وهو 7 --}}
    <tr><td colspan="7" class="px-6 py-4 text-center">لا توجد طلبات إجازة حالياً.</td></tr>
@endforelse
        </tbody>
    </table>
</div>
                 </div>
            </div>
        </div>
    </div>
    
    <!-- ======== Alpine.js script (Updated) ======== -->
    <script>
        function leaveForm() {
            return {
                leaveTypesData: @json($leaveTypes->keyBy('id')),
                selectedType: '',
                isDaily: false,
                isHourly: false,
                requiresAttachment: false,
                requiresDelegation: false,
                attachmentFields: [{ id: 1 }],
                nextAttachmentId: 2,
                
                updateFormVisibility() {
                    // Reset fields
                    document.getElementById('start_date_daily').value = '';
                    document.getElementById('end_date_daily').value = '';
                    document.getElementById('date_hourly').value = '';
                    document.getElementById('start_time').value = '';
                    document.getElementById('end_time').value = '';
                    if (!this.selectedType) {
                        this.isDaily = false;
                        this.isHourly = false;
                        this.requiresAttachment = false;
                        this.requiresDelegation = false;
                        return;
                    }
                    
                    const type = this.leaveTypesData[this.selectedType];
                    this.isDaily = type.unit === 'days';
                    this.isHourly = type.unit === 'hours';
                    this.requiresAttachment = type.requires_attachment == 1;
                    this.requiresDelegation = type.requires_delegation == 1;

                    if(this.isHourly) {
                        document.getElementById('date_hourly').name = 'start_date';
                        document.getElementById('start_date_daily').name = 'start_date_daily_disabled';
                    } else {
                        document.getElementById('date_hourly').name = 'start_date_hourly_disabled';
                        document.getElementById('start_date_daily').name = 'start_date';
                    }
                },
                addAttachmentField() {
                    this.attachmentFields.push({ id: this.nextAttachmentId++ });
                },
                removeAttachmentField(id) {
                    if (this.attachmentFields.length === 1) return;
                    this.attachmentFields = this.attachmentFields.filter(field => field.id !== id);
                }
            }
        }
    </script>
</x-app-layout>
