<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('تقديم سبب') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- رسالة توضيحية للمستخدم --}}
                    @if(session('info'))
                        <div class="mb-4 font-medium text-sm text-blue-600 bg-blue-100 p-3 rounded-md">
                            {{ session('info') }}
                        </div>
                    @endif

                    {{-- نموذج إدخال السبب --}}
                    <form action="{{ route('justification.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="record_id" value="{{ $record_id }}">

                        <div>
                            <x-input-label for="reason" :value="__('يرجى كتابة السبب بالتفصيل')" />
                            <textarea name="reason" id="reason" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="4" required autofocus></textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('إرسال') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
