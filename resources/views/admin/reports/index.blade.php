<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">{{ __('التقارير') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="border-b border-gray-200 mb-6">
                {{-- UPDATED: All tabs unified with custom active/inactive styles and responsive layout --}}
<nav class="-mb-px flex flex-wrap gap-x-4 gap-y-2 rtl:space-x-reverse" aria-label="Tabs">
    {{-- تقرير الحضور --}}
    <a href="{{ route('admin.reports.index', ['tab' => 'attendance']) }}"
       class="whitespace-nowrap py-3 px-2 font-medium text-sm"
       style="
           color: {{ $activeTab === 'attendance' ? '#caa453' : '#156b68' }};
           {{ $activeTab === 'attendance' ? 'border-bottom: 2px solid #caa453;' : 'border-bottom: none;' }};
           transition: all 0.3s ease;">
       تقرير الحضور
    </a>

    {{-- تقرير الإجازات --}}
    <a href="{{ route('admin.reports.index', ['tab' => 'leaves']) }}"
       class="whitespace-nowrap py-3 px-2 font-medium text-sm"
       style="
           color: {{ $activeTab === 'leaves' ? '#caa453' : '#156b68' }};
           {{ $activeTab === 'leaves' ? 'border-bottom: 2px solid #caa453;' : 'border-bottom: none;' }};
           transition: all 0.3s ease;">
       تقرير الإجازات
    </a>

    {{-- أرصدة الإجازات --}}
    <a href="{{ route('admin.reports.index', ['tab' => 'balances']) }}"
       class="whitespace-nowrap py-3 px-2 font-medium text-sm"
       style="
           color: {{ $activeTab === 'balances' ? '#caa453' : '#156b68' }};
           {{ $activeTab === 'balances' ? 'border-bottom: 2px solid #caa453;' : 'border-bottom: none;' }};
           transition: all 0.3s ease;">
       أرصدة الإجازات
    </a>

    {{-- الموظفين --}}
    <a href="{{ route('admin.reports.index', ['tab' => 'employees']) }}"
       class="whitespace-nowrap py-3 px-2 font-medium text-sm"
       style="
           color: {{ $activeTab === 'employees' ? '#caa453' : '#156b68' }};
           {{ $activeTab === 'employees' ? 'border-bottom: 2px solid #caa453;' : 'border-bottom: none;' }};
           transition: all 0.3s ease;">
       الموظفين
    </a>
</nav>

            </div>

            @if ($activeTab === 'attendance')
                @include('admin.reports.partials.attendance-report')
            @elseif ($activeTab === 'leaves')
                @include('admin.reports.partials.leaves-report')
            @elseif ($activeTab === 'balances')
                @include('admin.reports.partials.balances-report')
            @elseif ($activeTab === 'employees')
                @include('admin.reports.partials.employees-report')
            @endif
        </div>
    </div>

    <div id="imageModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden p-4" onclick="closeImageModal()">
        <div class="bg-white p-4 rounded-lg shadow-xl relative max-w-4xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-0 right-0 -m-3 text-white bg-gray-800 border-2 border-white rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold z-10 hover:bg-red-600 transition-colors">&times;</button>
            <div class="overflow-hidden rounded-md flex justify-center items-center min-h-[300px]">
                <img id="modalImage" src="" alt="صورة الموظف" style="max-width: 100%; max-height: 80vh; object-fit: contain; display: block;">
            </div>
        </div>
    </div>

    {{-- Modal for attendance selfies --}}
    @push('scripts')
    <script>
        function showImageModal(src) { document.getElementById('imageModal').classList.remove('hidden'); document.getElementById('modalImage').src = src; }
        function closeImageModal() { document.getElementById('imageModal').classList.add('hidden'); }
    </script>
    @endpush
</x-app-layout>