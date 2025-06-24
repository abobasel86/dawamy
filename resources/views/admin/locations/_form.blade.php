<div>
    <div class="mb-4">
        <label for="name" class="block font-medium text-sm text-gray-700">اسم الموقع</label>
        <input type="text" name="name" id="name" value="{{ old('name', $location->name ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
        <div>
            <label for="latitude" class="block font-medium text-sm text-gray-700">Latitude (خط العرض)</label>
            <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $location->latitude ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        </div>
        <div>
            <label for="longitude" class="block font-medium text-sm text-gray-700">Longitude (خط الطول)</label>
            <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $location->longitude ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        </div>
    </div>
    <div class="mb-4">
        <label for="radius_meters" class="block font-medium text-sm text-gray-700">نصف القطر المسموح به (بالمتر)</label>
        <input type="number" name="radius_meters" id="radius_meters" value="{{ old('radius_meters', $location->radius_meters ?? 100) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>
    <p class="text-sm text-gray-500 mb-4">يمكنك الحصول على إحداثيات أي موقع بسهولة من خرائط جوجل.</p>
    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('admin.locations.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">إلغاء</a>
        <button type="submit" class="btn-primary">
            حفظ البيانات
        </button>
    </div>
</div>
