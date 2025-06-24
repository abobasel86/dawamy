<x-app-layout>
    <x-slot name="header">
        {{ __('تعديل الموقع') }}
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.locations.update', $location) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.locations._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
