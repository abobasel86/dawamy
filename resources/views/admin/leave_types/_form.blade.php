<div>
    <!-- Leave Type Name -->
    <div class="mb-4">
        <label for="name" class="block font-medium text-sm text-gray-700">الاسم</label>
        <input type="text" name="name" id="name" value="{{ old('name', $leaveType->name ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>

    <!-- Default Balance -->
    <div class="mb-4">
        <label for="days_annually" class="block font-medium text-sm text-gray-700">الرصيد الافتراضي (أيام/ساعات)</label>
        <input type="number" name="days_annually" id="days_annually" value="{{ old('days_annually', $leaveType->days_annually ?? 0) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>

    <!-- Leave Unit -->
    <div class="mb-4">
        <label for="unit" class="block font-medium text-sm text-gray-700">وحدة الإجازة</label>
        <select name="unit" id="unit" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
            <option value="days" @selected(old('unit', $leaveType->unit ?? '') == 'days')>يومية</option>
            <option value="hours" @selected(old('unit', $leaveType->unit ?? '') == 'hours')>ساعية</option>
        </select>
    </div>

    <!-- Requires Attachment -->
    <div class="mb-4">
        <label for="requires_attachment" class="inline-flex items-center">
            <input type="checkbox" name="requires_attachment" id="requires_attachment" value="1" @checked(old('requires_attachment', $leaveType->requires_attachment ?? false)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">تتطلب إرفاق ملف (مثل تقرير طبي)</span>
        </label>
    </div>

    <!-- ==== الحقل الجديد لتمييز الإجازة السنوية ==== -->
    <div class="mb-4">
        <label for="is_annual" class="inline-flex items-center">
            <input type="checkbox" name="is_annual" id="is_annual" value="1" @checked(old('is_annual', $leaveType->is_annual ?? false)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600 font-bold">هذه هي الإجازة السنوية الرئيسية (تطبق عليها سياسة الرصيد التراكمي)</span>
        </label>
    </div>
	
    <!-- ==== الحقل الجديد لتحديد خاصية التفويض ==== -->
	<div class="mb-4">
        <label for="requires_delegation" class="inline-flex items-center">
            <input type="checkbox" name="requires_delegation" id="requires_delegation" value="1" @checked(old('requires_delegation', $leaveType->requires_delegation ?? false))>
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">تتطلب تحديد موظف مفوض</span>
        </label>
    </div>
	
	<!-- ==== الحقل الجديد لتحديد خاصية إظهار الرصيد ==== -->
    <div class="mb-4">
        <label for="show_in_balance" class="inline-flex items-center">
            <input type="checkbox" name="show_in_balance" id="show_in_balance" value="1" @checked(old('show_in_balance', $leaveType->show_in_balance ?? true))> <!-- Checked by default -->
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">إظهار الرصيد لهذا النوع من الإجازات</span>
        </label>
    </div>
	
	<!-- ==== الحقل الجديد لإظهار الأيام المأخوذة ==== -->
	<div class="mb-4">
        <label for="show_taken_in_report" class="inline-flex items-center">
            <input type="checkbox" name="show_taken_in_report" id="show_taken_in_report" value="1" @checked(old('show_taken_in_report', $leaveType->show_taken_in_report ?? true))>
            <span class="ml-2 rtl:mr-2 text-sm text-gray-600">إظهار عدد الأيام/الساعات المأخوذة في تقرير الأرصدة</span>
        </label>
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('admin.leave-types.index') }}" class="text-gray-600 hover:text-gray-900 mr-4 rtl:ml-4">إلغاء</a>
        <button type="submit" class="btn-primary">
            حفظ البيانات
        </button>
    </div>
</div>
