<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('العطل الرسمية') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.holidays.create') }}" class="btn-primary">إضافة عطلة جديدة</a>
            </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>اسم العطلة</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                    <tr>
                        <td>{{ $holiday->name }}</td>
                        <td>{{ $holiday->date->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">لا توجد عطل رسمية مضافة حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
       </div>
    </div>
</x-app-layout>