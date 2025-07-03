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