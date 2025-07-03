 <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('تعديل العطلة الرسمية') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
        <form action="{{ route('admin.holidays.update', $holiday) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">اسم العطلة</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $holiday->name) }}" required>
            </div>
            <div class="form-group">
                <label for="date">التاريخ</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $holiday->date->format('Y-m-d')) }}" required>
            </div>
            <button type="submit" class="btn btn-success mt-3">تحديث</button>
        </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>