<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('الملف الشخصي') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            تفعيل إشعارات المتصفح
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            فعّل الإشعارات ليصلك كل جديد مباشرة على جهازك حتى لو كان المتصفح أو التطبيق مغلقاً.
                        </p>
                    </header>
                    <button id="enable-push-btn" class="btn-primary mt-4">تفعيل الإشعارات</button>
                </section>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- =================================================================== --}}
{{-- ===== الكود الجديد والمباشر لتشغيل الزر (تمت إزالة @push) ===== --}}
{{-- =================================================================== --}}
<script>
function pushNotifications() {
    return {
        isPushEnabled: {{ auth()->user()->pushSubscriptions()->count() > 0 ? 'true' : 'false' }},
        pushButtonDisabled: true,

        init() {
            if (!('serviceWorker' in navigator && 'PushManager' in window)) {
                console.warn('Push messaging is not supported');
                this.pushButtonDisabled = true;
                return;
            }

            navigator.serviceWorker.ready.then(() => {
                this.pushButtonDisabled = false;
            });
        },

        enableNotifications() {
            this.pushButtonDisabled = true;
            navigator.serviceWorker.getRegistration().then(registration => {
                const subscribeOptions = {
                    userVisibleOnly: true,
                    applicationServerKey: '{{ config('webpush.vapid.public_key') }}'
                };
                return registration.pushManager.subscribe(subscribeOptions);
            }).then(pushSubscription => {
                this.storePushSubscription(pushSubscription);
                this.isPushEnabled = true;
                this.pushButtonDisabled = false;
            }).catch(error => {
                console.error('Error subscribing for push notifications', error);
                this.pushButtonDisabled = false;
            });
        },

        disableNotifications() {
            this.pushButtonDisabled = true;
            navigator.serviceWorker.getRegistration().then(registration => {
                return registration.pushManager.getSubscription();
            }).then(subscription => {
                if (subscription) {
                    return subscription.unsubscribe();
                }
            }).then(() => {
                this.deletePushSubscription();
                this.isPushEnabled = false;
                this.pushButtonDisabled = false;
            }).catch(error => {
                console.error('Error unsubscribing from push notifications', error);
                this.pushButtonDisabled = false;
            });
        },

        storePushSubscription(subscription) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.post('/push-subscriptions', subscription, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            }).then(response => {
                console.log('Subscription stored');
            }).catch(error => {
                console.error('Error storing push subscription', error);
            });
        },

        deletePushSubscription() {
            navigator.serviceWorker.getRegistration().then(registration => {
                return registration.pushManager.getSubscription();
            }).then(subscription => {
                if (subscription) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    axios.post('/push-subscriptions/delete', {
                        endpoint: subscription.endpoint
                    }, {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        }
                    }).then(response => {
                        console.log('Subscription deleted');
                    }).catch(error => {
                        console.error('Error deleting push subscription', error);
                    });
                }
            });
        }
    };
}
</script>