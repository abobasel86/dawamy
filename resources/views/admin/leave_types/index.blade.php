<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('إدارة أنواع الإجازات') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.leave-types.create') }}" class="btn-primary">
                    إضافة نوع إجازة جديد
                </a>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرصيد الافتراضي</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوحدة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">يتطلب مرفق</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($leaveTypes as $type)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $type->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $type->days_annually }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $type->unit === 'days' ? 'يومية' : 'ساعية' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $type->requires_attachment ? 'نعم' : 'لا' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap flex space-x-2 space-x-reverse">
                                            <a href="{{ route('admin.leave-types.edit', $type) }}" class="text-indigo-600 hover:text-indigo-900">تعديل</a>
                                            <form method="POST" action="{{ route('admin.leave-types.destroy', $type) }}" onsubmit="return confirm('هل أنت متأكد من رغبتك في الحذف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">لا توجد أنواع إجازات معرفة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>