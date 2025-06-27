<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo2.png') }}" alt="شعار الشركة" class="block h-9 w-auto">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link-custom">
                        {{ __('لوحة التحكم') }}
                    </x-nav-link>
                    <x-nav-link :href="route('attendance.history')" :active="request()->routeIs('attendance.history')" class="nav-link-custom">
                        {{ __('سجل الدوام') }}
                    </x-nav-link>
                    <x-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')" class="nav-link-custom">
                        {{ __('الإجازات') }}
                    </x-nav-link>
                    @role('manager|admin|secretary_general|assistant_secretary_general')
                        <x-nav-link :href="route('manager.approvals.index')" :active="request()->routeIs('manager.approvals.index')" class="nav-link-custom">
                            {{ __('الموافقات') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.team.index')" :active="request()->routeIs('manager.team.index')" class="nav-link-custom">
                            {{ __('فريق العمل') }}
                        </x-nav-link>
                    @endrole
                    @role('admin')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="nav-link-custom">
                            {{ __('إدارة المستخدمين') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')" class="nav-link-custom">
                            {{ __('إدارة الأقسام') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('admin.leave-types.index')" :active="request()->routeIs('admin.leave-types.*')" class="nav-link-custom">
                            {{ __('أنواع الإجازات') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.document-types.index')" :active="request()->routeIs('admin.document-types.*')" class="nav-link-custom">
                            {{ __('أنواع المستندات') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')" class="nav-link-custom">
                            {{ __('إدارة المواقع') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" class="nav-link-custom">
                            {{ __('التقارير') }}
                        </x-nav-link>
                    @endrole
                    @role('HR')
                        <x-nav-link :href="route('manager.team.index')" :active="request()->routeIs('manager.team.index')" class="nav-link-custom">
                            {{ __('فريق العمل') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="nav-link-custom">
                            {{ __('إدارة المستخدمين') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" class="nav-link-custom">
                            {{ __('التقارير') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

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

                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('الملف الشخصي') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); document.getElementById('logout-form').dispatchEvent(new Event('submit', { cancelable: true }));">
                                    {{ __('تسجيل الخروج') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden" x-data="notificationsComponent()" x-init="init()">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">لوحة التحكم</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.history')" :active="request()->routeIs('attendance.history')">سجل الدوام</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')">الإجازات</x-responsive-nav-link>
            @role('manager|admin|secretary_general|assistant_secretary_general')
                <x-responsive-nav-link :href="route('manager.approvals.index')" :active="request()->routeIs('manager.approvals.index')">الموافقات</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.team.index')" :active="request()->routeIs('manager.team.index')">فريق العمل</x-responsive-nav-link>
            @endrole
            @role('admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">إدارة المستخدمين</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')">إدارة الأقسام</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.leave-types.index')" :active="request()->routeIs('admin.leave-types.*')">أنواع الإجازات</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.document-types.index')" :active="request()->routeIs('admin.document-types.*')">أنواع المستندات</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">إدارة المواقع</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">التقارير</x-responsive-nav-link>
            @endrole
            @role('HR')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">إدارة المستخدمين</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">التقارير</x-responsive-nav-link>
            @endrole
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex justify-between items-center">
                <div class="font-medium text-base text-gray-800">الإشعارات</div>
                <div x-show="totalUnread > 0" x-text="totalUnread" class="px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full"></div>
            </div>
            <div class="mt-3 space-y-1">
                <template x-if="latestNotifications.length === 0"><div class="px-4 py-2 text-sm text-gray-500">لا توجد إشعارات جديدة.</div></template>
                <template x-for="notification in latestNotifications" :key="notification.id">
                    <a  :href="notification.data.url ? notification.data.url : '#'" 
                        @click.prevent="read(notification)" 
                        class="block w-full ps-4 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium transition duration-150 ease-in-out"
                        :class="!notification.read_at ? 'text-gray-800 bg-blue-50 border-blue-400 font-bold' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'">
                        <p x-text="notification.data.message"></p>
                        <small class="text-xs" :class="!notification.read_at ? 'text-blue-600' : 'text-gray-500'" x-text="new Date(notification.created_at).toLocaleString('ar-EG')"></small>
                    </a>
                </template>
                <x-responsive-nav-link :href="route('notifications.index')">عرض كل الإشعارات</x-responsive-nav-link>
            </div>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
    <x-responsive-nav-link :href="route('profile.edit')">
        {{ __('الملف الشخصي') }}
    </x-responsive-nav-link>

    {{-- START: التصحيح هنا --}}
    <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile">
        @csrf

        <x-responsive-nav-link :href="route('logout')"
                onclick="event.preventDefault(); 
                         document.getElementById('logout-form-mobile').dispatchEvent(new Event('submit', { cancelable: true }));">
            {{ __('تسجيل الخروج') }}
        </x-responsive-nav-link>
    </form>
    {{-- END: التصحيح هنا --}}
</div>
        </div>
    </div>
    </nav>