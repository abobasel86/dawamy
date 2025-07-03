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
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-nav-link>
                    <x-nav-link :href="route('attendance.history')" :active="request()->routeIs('attendance.history')">
                        {{ __('سجل الدوام') }}
                    </x-nav-link>
                    <x-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')">
                        {{ __('الإجازات') }}
                    </x-nav-link>
                    <x-nav-link :href="route('overtime.my-requests')" :active="request()->routeIs('overtime.my-requests')">
                        {{ __('طلباتي للعمل الإضافي') }}
                    </x-nav-link>

                    {{-- قائمة الموافقات للمدراء --}}
                    @role('manager|admin|secretary_general|assistant_secretary_general')
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('manager.approvals.*') || request()->routeIs('admin.overtime.approvals.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>الموافقات</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('manager.approvals.index')">
                                        {{ __('موافقات الإجازات') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.overtime.approvals.index')">
                                        {{ __('موافقات الإضافي') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                         <x-nav-link :href="route('manager.team.index')" :active="request()->routeIs('manager.team.index')">
                            {{ __('فريق العمل') }}
                        </x-nav-link>
                    @endrole
                    
                    {{-- قائمة الإعدادات للأدمن --}}
                    @role('admin')
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>إعدادات النظام</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.users.index')">إدارة المستخدمين</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.departments.index')">إدارة الأقسام</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.locations.index')">إدارة المواقع</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.work-shifts.index')">أنماط الدوام</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.leave-types.index')">أنواع الإجازات</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.document-types.index')">أنواع المستندات</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.holidays.index')">العطل الرسمية</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endrole

                    @role('admin|HR|secretary_general')
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('التقارير') }}
                        </x-nav-link>
                    @endrole

                    {{-- رابط تقرير المالية --}}
                    @can('view-finance-reports')
                         <x-nav-link :href="route('finance.overtime.report')" :active="request()->routeIs('finance.overtime.report')">
                            {{ __('التقارير المالية') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- كود الإشعارات والملف الشخصي (يبقى كما هو) --}}
                @include('layouts.partials.notifications-dropdown')
                @include('layouts.partials.user-dropdown')
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('لوحة التحكم') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.history')" :active="request()->routeIs('attendance.history')">
                {{ __('سجل الدوام') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')">
                {{ __('الإجازات') }}
            </x-responsive-nav-link>

             @role('manager|admin|secretary_general|assistant_secretary_general')
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4"><div class="font-medium text-base text-gray-800">الموافقات</div></div>
                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('manager.approvals.index')">موافقات الإجازات</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.overtime.approvals.index')">موافقات الإضافي</x-responsive-nav-link>
                    </div>
                </div>
                 <x-responsive-nav-link :href="route('manager.team.index')" :active="request()->routeIs('manager.team.index')">
                    {{ __('فريق العمل') }}
                </x-responsive-nav-link>
            @endrole

            @role('admin')
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4"><div class="font-medium text-base text-gray-800">إعدادات النظام</div></div>
                    <div class="mt-3 space-y-1">
                         <x-responsive-nav-link :href="route('admin.users.index')">إدارة المستخدمين</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.departments.index')">إدارة الأقسام</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.locations.index')">إدارة المواقع</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.work-shifts.index')">أنماط الدوام</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.leave-types.index')">أنواع الإجازات</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.document-types.index')">أنواع المستندات</x-responsive-nav-link>
                         <x-responsive-nav-link :href="route('admin.holidays.index')">العطل الرسمية</x-responsive-nav-link>
                    </div>
                </div>
            @endrole

            @role('admin|HR|secretary_general')
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                    {{ __('التقارير') }}
                </x-responsive-nav-link>
            @endrole

             @can('view-finance-reports')
                <x-responsive-nav-link :href="route('finance.overtime.report')" :active="request()->routeIs('finance.overtime.report')">
                    {{ __('التقارير المالية') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            @include('layouts.partials.responsive-user-options')
        </div>
    </div>
</nav>