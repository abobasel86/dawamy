<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('تقرير أرصدة الإجازات') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th rowspan="2" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase align-middle">اسم الموظف</th>
                                    @foreach ($leaveTypes as $leaveType)
                                        <th colspan="{{ $leaveType->show_taken_in_report ? 2 : 1 }}" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-b border-l">{{ $leaveType->name }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($leaveTypes as $leaveType)
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l">الرصيد المتبقي</th>
                                        @if($leaveType->show_taken_in_report)
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l">المأخوذ هذا العام</th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($balanceData as $data)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $data['name'] }}</td>
                                        @foreach ($leaveTypes as $leaveType)
                                            <td class="px-6 py-4 whitespace-nowrap text-center border-l">
                                                {{ $data['balances'][$leaveType->id]['balance'] }}
                                                <span class="text-xs text-gray-500">{{ $data['balances'][$leaveType->id]['unit'] === 'days' ? 'يوم' : 'ساعة' }}</span>
                                            </td>
                                            @if($leaveType->show_taken_in_report)
                                                <td class="px-6 py-4 whitespace-nowrap text-center border-l bg-gray-50">
                                                    {{ $data['balances'][$leaveType->id]['taken'] }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($leaveTypes) * 2 + 1 }}" class="px-6 py-4 text-center">لا يوجد بيانات لعرضها.</td>
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