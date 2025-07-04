<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">إضافة نمط دوام جديد</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.work-shifts.store') }}">
                        @csrf
                        @include('admin.work_shifts._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
