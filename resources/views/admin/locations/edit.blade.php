<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            تعديل الموقع: {{ $location->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                           <p class="font-bold">يرجى تصحيح الأخطاء التالية:</p>
                           <ul class="list-disc list-inside mt-2">
                               @foreach ($errors->all() as $error)
                                   <li>{{ $error }}</li>
                               @endforeach
                           </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.locations.update', $location->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Location Name -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">اسم الموقع</label>
                                <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="name" value="{{ old('name', $location->name) }}" required autofocus />
                            </div>

                            <!-- Work Shift -->
                            <div>
                                <label for="work_shift_id" class="block font-medium text-sm text-gray-700">نمط الدوام الافتراضي</label>
                                <select name="work_shift_id" id="work_shift_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">-- بلا نمط افتراضي --</option>
                                    @foreach($workShifts as $shift)
                                        <option value="{{ $shift->id }}" @selected(old('work_shift_id', $location->work_shift_id) == $shift->id)>
                                            {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Timezone -->
                            <div>
                                <label for="timezone" class="block font-medium text-sm text-gray-700">المنطقة الزمنية</label>
                                <select name="timezone" id="timezone" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $location->timezone) == $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Radius -->
                             <div>
                                <label for="radius_meters" class="block font-medium text-sm text-gray-700">نطاق الموقع المسموح (بالمتر)</label>
                                <input id="radius_meters" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="number" name="radius_meters" value="{{ old('radius_meters', $location->radius_meters) }}" required />
                            </div>

                             <!-- Latitude -->
                            <div>
                                <label for="latitude" class="block font-medium text-sm text-gray-700">خط العرض (Latitude)</label>
                                <input id="latitude" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="latitude" value="{{ old('latitude', $location->latitude) }}" required />
                            </div>

                            <!-- Longitude -->
                             <div>
                                <label for="longitude" class="block font-medium text-sm text-gray-700">خط الطول (Longitude)</label>
                                <input id="longitude" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="longitude" value="{{ old('longitude', $location->longitude) }}" required />
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.locations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">إلغاء</a>
                            <button type="submit" class="btn-primary">
                                تحديث الموقع
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
