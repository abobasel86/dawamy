<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
    <a href="{{ route('dashboard') }}">
        {{-- ===== تم استبدال الشعار هنا ===== --}}
        <img src="{{ asset('images/logo2.png') }}" alt="شعار الشركة" class="block h-9 w-auto">
    </a>
</div>

                <!-- Navigation Links -->
				
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
                        
                        <x-nav-link :href="route('admin.balances.index')" :active="request()->routeIs('admin.balances.index')" class="nav-link-custom">
                            {{ __('تقرير الأرصدة') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" class="nav-link-custom">
                            {{ __('تصدير التقارير') }}
                        </x-nav-link> 
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:me-6">
			<div class="ms-3 relative">
                <x-dropdown align="right" width="96"> {{-- يمكنك تعديل عرض القائمة من هنا --}}
                    <x-slot name="trigger">
                        <button class="relative inline-flex items-center p-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if(isset($user_notifications) && $user_notifications->whereNull('read_at')->count() > 0)
                                    <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -right-2">
                                        {{ $user_notifications->whereNull('read_at')->count() }}
                                    </div>
                                @endif
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="p-2 font-bold border-b">الإشعارات</div>
                            @forelse($user_notifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}" class="block w-full px-4 py-3 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 transition border-b">
                                    <p class="{{ $notification->read_at ? 'font-normal' : 'font-bold' }}">
                                        {{ data_get($notification, 'data.message') }}
                                    </p>
                                    <div class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                </a>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-500">لا توجد إشعارات جديدة.</div>
                            @endforelse
                            <div class="border-t">
                                <a href="{{ route('notifications.index') }}" class="block w-full text-center px-4 py-2 text-xs text-blue-600 hover:bg-gray-100">عرض كل الإشعارات</a>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- المستخدم -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('الملف الشخصي') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('تسجيل الخروج') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- قائمة الهاتف -->
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

    <!-- قائمة الهاتف المنسدلة -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
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
                <x-responsive-nav-link :href="route('admin.balances.index')" :active="request()->routeIs('admin.balances.index')">تقرير الأرصدة</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">تصدير التقارير</x-responsive-nav-link>
            @endrole
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex justify-between items-center">
                <div class="font-medium text-base text-gray-800">الإشعارات</div>
                @if(isset($user_notifications) && $user_notifications->whereNull('read_at')->count() > 0)
                    <span class="px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">
                        {{ $user_notifications->whereNull('read_at')->count() }}
                    </span>
                @endif
            </div>
            <div class="mt-3 space-y-1">
                @forelse($user_notifications as $notification)
                    <x-responsive-nav-link href="{{ route('notifications.read', $notification->id) }}">
                        <p class="{{ $notification->read_at ? 'font-normal' : 'font-bold' }}">{{ data_get($notification, 'data.message') }}</p>
                        <small class="text-gray-500">{{ $notification->created_at->diffForHumans() }}</small>
                    </x-responsive-nav-link>
                @empty
                    <div class="px-4 text-sm text-gray-500">لا توجد إشعارات جديدة.</div>
                @endforelse
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

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')"
                    onclick="event.preventDefault();
                                this.closest('form').submit();">
                {{ __('تسجيل الخروج') }}
            </x-responsive-nav-link>
        </form>
    </div>
</div>
    </div>
</nav>

