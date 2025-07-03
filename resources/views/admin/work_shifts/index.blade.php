<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            إدارة أنماط الدوام
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                 <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.work-shifts.create') }}" class="btn-primary">إضافة نمط دوام جديد</a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت البدء</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الانتهاء</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">فترة السماح (قبل)</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">فترة السماح (بعد)</th>
        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
    </tr>
</thead>
                            <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($workShifts as $shift)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">{{ $shift->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
            <!-- الإضافة هنا -->
            <td class="px-6 py-4 whitespace-nowrap">{{ $shift->grace_period_before_start_minutes }} دقيقة</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $shift->grace_period_after_start_minutes }} دقيقة</td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                @if($shift->is_active)
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">فعال</span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">غير فعال</span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <a href="{{ route('admin.work-shifts.edit', $shift->id) }}" class="text-indigo-600 hover:text-indigo-900">تعديل</a>
                    <form action="{{ route('admin.work-shifts.destroy', $shift->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <!-- تم تعديل colspan ليناسب العدد الجديد للأعمدة -->
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                لا توجد أنماط دوام معرفة حالياً.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $workShifts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
