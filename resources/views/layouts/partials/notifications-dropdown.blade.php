<div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <div x-data="notificationsComponent()" x-init="init()" class="relative">
                    <button @click="isOpen = !isOpen" class="relative inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 rounded-lg hover:text-gray-900 focus:outline-none">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 14 20">
                            <path d="M12.133 10.632v-1.8A5.406 5.406 0 0 0 7.979 3.57.946.946 0 0 0 8 3.464V1.1a1 1 0 0 0-2 0v2.364a.946.946 0 0 0 .021.106 5.406 5.406 0 0 0-4.154 5.262v1.8C1.867 13.018 0 13.614 0 14.807 0 15.4 0 16 .538 16h12.924C14 16 14 15.4 14 14.807c0-1.193-1.867-1.789-1.867-4.175ZM3.823 17a3.453 3.453 0 0 0 6.354 0H3.823Z"/>
                        </svg>
                        <div x-show="totalUnread > 0" x-text="totalUnread" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -end-2"></div>
                    </button>
                    <div x-show="isOpen" @click.away="isOpen = false" x-transition class="absolute end-0 z-50 mt-2 w-80 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;">
                        <div class="block px-4 py-2 text-sm font-medium text-center text-gray-700 rounded-t-lg bg-gray-50">الإشعارات</div>
                        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            <template x-if="latestNotifications.length === 0"><p class="p-4 text-sm text-center text-gray-500">لا توجد إشعارات لعرضها.</p></template>
                            <template x-for="notification in latestNotifications" :key="notification.id">
                                <a :href="notification.data.url || '#'" @click.prevent="read(notification)" class="flex px-4 py-3 hover:bg-gray-100" :class="!notification.read_at ? 'bg-blue-50' : ''">
                                    <div class="w-full ps-3">
                                        <div class="text-gray-500 text-sm mb-1.5" :class="!notification.read_at ? 'font-bold text-gray-900' : ''" x-text="notification.data.message"></div>
                                        <div class="text-xs text-blue-600" x-text="new Date(notification.created_at).toLocaleString('ar-EG')"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="block py-2 text-sm font-medium text-center text-gray-900 rounded-b-lg bg-gray-50 hover:bg-gray-100">عرض الكل</a>
                    </div>
                </div>