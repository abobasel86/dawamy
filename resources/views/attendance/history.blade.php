<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('سجل الحضور والانصراف') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اليوم والتاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الحضور</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وقت الانصراف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">جهاز الحضور</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منصة الحضور</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">جهاز الانصراف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منصة الانصراف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مدة العمل</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($attendanceLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->punch_in_time)->translatedFormat('l, d F Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->punch_in_time)->format('h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->punch_out_time)
                                                {{ \Carbon\Carbon::parse($log->punch_out_time)->format('h:i A') }}
                                            @else
                                                <span class="text-gray-500">لم يسجل انصراف</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->punch_in_device ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->punch_in_platform ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->punch_out_device ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->punch_out_platform ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->punch_out_time)
                                                @php
                                                    $punchIn = \Carbon\Carbon::parse($log->punch_in_time);
                                                    $punchOut = \Carbon\Carbon::parse($log->punch_out_time);
                                                    $diff = $punchIn->diff($punchOut);
                                                    printf('%02d:%02d', $diff->h, $diff->i);
                                                @endphp
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center">لا يوجد سجلات حضور لعرضها.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- روابط التنقل بين الصفحات --}}
                    <div class="mt-4">
                        {{ $attendanceLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
