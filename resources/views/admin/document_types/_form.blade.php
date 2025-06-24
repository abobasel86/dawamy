<div>
    <div class="mb-4">
        <label for="name" class="block font-medium text-sm text-gray-700">اسم نوع المستند</label>
        <input type="text" name="name" id="name" value="{{ old('name', $documentType->name ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>
    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('admin.document-types.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">إلغاء</a>
        <button type="submit" class="btn-primary">حفظ</button>
    </div>
</div>