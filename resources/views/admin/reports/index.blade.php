<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('تقارير الحضور والانصراف') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">فلترة التقرير</h3>

                    <form method="GET" action="{{ route('admin.reports.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="user_ids" class="block font-medium text-sm text-gray-700">الموظف</label>
                                <select name="user_ids[]" id="user_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    {{-- يمكنك إضافة مكتبة مثل Tom-select أو Select2 هنا لتحسين شكل القائمة --}}
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
                            <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إعادة تعيين</a>
                            <button type="submit" class="btn-primary">
                                عرض التقرير
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">نتائج التقرير</h3>
                        @if(request()->has('start_date'))
                            <a href="{{ route('admin.reports.export.attendance', request()->query()) }}" class="btn-secondary">
                               تصدير النتائج الحالية
                            </a>
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
        <button type="button" onclick="showImageModal('{{ asset('storage/' . $log->punch_in_selfie_path) }}')" class="text-indigo-600 hover:text-indigo-900 hover:underline text-sm font-semibold">
            عرض الصورة
        </button>
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
        <button type="button" onclick="showImageModal('{{ asset('storage/' . $log->punch_out_selfie_path) }}')" class="text-indigo-600 hover:text-indigo-900 hover:underline text-sm font-semibold">
            عرض الصورة
        </button>
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
    </div>
    
    <div id="imageModal" 
     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden p-4" 
     onclick="closeImageModal()">
    
    <div class="bg-white p-4 rounded-lg shadow-xl relative max-w-4xl w-full max-h-[90vh] overflow-auto" 
         onclick="event.stopPropagation()">

        <button onclick="closeImageModal()" 
                class="absolute top-0 right-0 -m-3 text-white bg-gray-800 border-2 border-white rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold z-10 hover:bg-red-600 transition-colors">
            &times;
        </button>

        <div class="overflow-hidden rounded-md flex justify-center items-center min-h-[300px]">
            <img id="modalImage" 
                 src="" 
                 alt="صورة الموظف" 
                 style="max-width: 100%; max-height: 80vh; object-fit: contain; display: block;">
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
        // إغلاق النافذة عند الضغط خارج الصورة
        document.getElementById('imageModal').onclick = closeImageModal;
    </script>
    @endpush

</x-app-layout>