<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('كل الإشعارات') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if($notifications->isEmpty())
                        <p class="text-center text-gray-500">لا يوجد إشعارات لعرضها.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($notifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                   class="notification-link block p-4 rounded-lg transition duration-300 ease-in-out {{ $notification->read_at ? 'bg-gray-100' : 'bg-blue-50 hover:bg-blue-100' }}"
                                   data-notification-id="{{ $notification->id }}">
                                    
                                    <div class="flex justify-between items-center">
                                        <p class="notification-text {{ $notification->read_at ? 'font-normal text-gray-600' : 'font-bold text-gray-800' }}">
                                            {{ $notification->data['message'] }}
                                        </p>
                                        <span class="text-xs text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $notifications->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationLinks = document.querySelectorAll('.notification-link');

            notificationLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const notificationText = this.querySelector('.notification-text').textContent.trim();
                    const isUnread = !this.classList.contains('bg-gray-100');

                    // 🎯 الشرط: إذا كان الإشعار غير مقروء ويحتوي على كلمة "تفويضك"
                    if (isUnread && notificationText.includes('تفويضك')) {
                        // 1. منع الانتقال إلى الصفحة الأخرى
                        event.preventDefault();

                        const notificationId = this.dataset.notificationId;
                        const notificationElement = this; // حفظ العنصر لتغيير شكله لاحقاً

                        // 2. إرسال طلب للخادم لتعليم الإشعار كمقروء فقط
                        fetch(`/notifications/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'ngrok-skip-browser-warning': 'true'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // 3. تغيير شكل الإشعار في الصفحة الحالية بنجاح
                                const textElement = notificationElement.querySelector('.notification-text');
                                notificationElement.classList.remove('bg-blue-50', 'hover:bg-blue-100');
                                notificationElement.classList.add('bg-gray-100');
                                textElement.classList.remove('font-bold', 'text-gray-800');
                                textElement.classList.add('font-normal', 'text-gray-600');
                            }
                        })
                        .catch(error => {
                            console.error('There was a problem marking the notification as read:', error);
                        });
                    }
                    // إذا لم يتحقق الشرط، سيتم تنفيذ السلوك الافتراضي للرابط (الانتقال للصفحة الأخرى)
                });
            });
        });
    </script>
    @endpush
</x-app-layout>