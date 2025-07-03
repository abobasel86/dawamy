<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">اسم نمط الدوام</label>
        <input type="text" name="name" id="name" value="{{ old('name', $workShift->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">وقت بدء الدوام</label>
            <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $workShift->start_time ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
        </div>
        <div>
            <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">وقت انتهاء الدوام</label>
            <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $workShift->end_time ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">الوصف (اختياري)</label>
        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">{{ old('description', $workShift->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="grace_period_before_start_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">فترة السماح قبل بدء الدوام (بالدقائق)</label>
            <input type="number" name="grace_period_before_start_minutes" id="grace_period_before_start_minutes" value="{{ old('grace_period_before_start_minutes', $workShift->grace_period_before_start_minutes ?? 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">المدة التي لا يعتبر عملها إضافياً إذا بدأ الموظف قبل بدء الدوام. اتركها 0 إذا لم تكن هناك فترة سماح.</p>
        </div>
        
        <div>
            <label for="grace_period_after_start_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">فترة السماح بعد بدء الدوام (بالدقائق)</label>
            <input type="number" name="grace_period_after_start_minutes" id="grace_period_after_start_minutes" value="{{ old('grace_period_after_start_minutes', $workShift->grace_period_after_start_minutes ?? 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">المدة التي لا يعتبر فيها الموظف متأخراً بعد بدء الدوام. اتركها 0 إذا لم تكن هناك فترة سماح.</p>
        </div>
    </div>


    <div class="flex items-center">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $workShift->is_active ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="is_active" class="ms-2 block text-sm text-gray-900 dark:text-gray-300">فعال</label>
    </div>
</div>

<div class="flex justify-end mt-6">
    <a href="{{ route('admin.work-shifts.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md me-2 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">إلغاء</a>
<button type="submit"
        dir="rtl"
        class="w-auto px-5 py-2.5 rounded-full font-semibold text-white transition-transform duration-200 hover:scale-105 shadow-md"
        style="background-color: #156b68; font-family: 'Tajawal', sans-serif;">
    حفظ
</button>
</div>
