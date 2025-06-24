<div>
    <div class="mb-4">
        <label for="name" class="block font-medium text-sm text-gray-700">اسم القسم</label>
        <input type="text" name="name" id="name" value="{{ old('name', $department->name ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>
    <div class="mb-4">
        <label for="manager_id" class="block font-medium text-sm text-gray-700">مدير القسم</label>
        <select name="manager_id" id="manager_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            <option value="">-- اختر مديراً --</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" @selected(old('manager_id', $department->manager_id ?? '') == $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="mb-4">
        <label for="requires_assistant_approval" class="inline-flex items-center">
            <input type="checkbox" name="requires_assistant_approval" id="requires_assistant_approval" value="1" @checked(old('requires_assistant_approval', $department->requires_assistant_approval ?? false))>
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">يتطلب موافقة الأمين العام المساعد</span>
        </label>
    </div>

    <!-- ==== الحقل الجديد للسماح بالتفويض الخارجي ==== -->
    <div class="mb-4">
        <label for="allow_cross_delegation" class="inline-flex items-center">
            <input type="checkbox" name="allow_cross_delegation" id="allow_cross_delegation" value="1" @checked(old('allow_cross_delegation', $department->allow_cross_delegation ?? false))>
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">السماح بتفويض موظفين من خارج القسم</span>
        </label>
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('admin.departments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">إلغاء</a>
        <button type="submit" class="btn-primary">حفظ</button>
    </div>
</div>