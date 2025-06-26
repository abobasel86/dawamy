<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('كل الإشعارات') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-0">
                    @forelse($notifications as $notification)
                        {{-- === START: التصحيح هنا === --}}
                        <a href="{{ route('notifications.read', $notification->id) }}" class="block p-4 border-b hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                        {{-- === END: التصحيح هنا === --}}
                            <p class="{{ $notification->read_at ? 'font-normal' : 'font-bold' }} text-gray-800">{{ data_get($notification, 'data.message') }}</p>
                            <small class="text-gray-500">{{ $notification->created_at->diffForHumans() }}</small>
                        </a>
                    @empty
                        <p class="text-center text-gray-500 py-10">لا يوجد لديك أي إشعارات.</p>
                    @endforelse
                    
                    <div class="p-4">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
