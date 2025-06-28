<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('الملف الشخصي') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="max-w-xl">
        <section class="space-y-6">
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    تسجيل جهاز بيومتري
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    سجّل جهازك (لابتوب أو موبايل) الذي يدعم البصمة أو الوجه لاستخدامه في تسجيل الحضور والانصراف بشكل آمن وسريع.
                </p>
            </header>

            <button type="button" id="register-webauthn-device" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                تسجيل هذا الجهاز
            </button>
        </section>
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

    {{-- ====================================================================== --}}
    {{-- ==== START: JAVASCRIPT FOR PUSH NOTIFICATIONS (ALPINE.JS) ==== --}}
    {{-- ====================================================================== --}}
    @push('scripts')
    <script>
        // دالة لترميز المفتاح العام (VAPID Public Key)
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // المكون الرئيسي لإدارة الإشعارات باستخدام Alpine.js
        document.addEventListener('alpine:init', () => {
            Alpine.data('pushManager', () => ({
                isPushEnabled: false,
                isProcessing: false,
                pushSupportError: '',
                
                // يتم استدعاؤها عند تحميل الصفحة
                async init() {
                    if (!('serviceWorker' in navigator && 'PushManager' in window)) {
                        this.pushSupportError = 'عذراً، الإشعارات الفورية غير مدعومة في هذا المتصفح.';
                        return;
                    }

                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();
                        this.isPushEnabled = subscription !== null;
                    } catch (error) {
                        console.error('Error checking push subscription status:', error);
                        this.pushSupportError = 'لم نتمكن من التحقق من حالة الإشعارات.';
                    }
                },

                // دالة للاشتراك في الإشعارات
                async subscribe() {
                    this.isProcessing = true;
                    this.pushSupportError = '';

                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const vapidPublicKey = "{{ config('webpush.vapid.public_key') }}";

                        if (!vapidPublicKey) {
                            console.error('VAPID public key is not set in .env file.');
                            this.pushSupportError = 'خطأ في الإعدادات من جانب الخادم.';
                            this.isProcessing = false;
                            return;
                        }

                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                        });
                        
                        await fetch('/push-subscriptions', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(subscription),
                        });

                        this.isPushEnabled = true;

                    } catch (error) {
                        console.error('Error subscribing to push notifications:', error);
                        if (error.name === 'NotAllowedError') {
                            this.pushSupportError = 'لقد رفضت إذن الإشعارات. يرجى تفعيله من إعدادات المتصفح.';
                        } else {
                            this.pushSupportError = 'فشل الاشتراك في الإشعارات.';
                        }
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // دالة لإلغاء الاشتراك
                async unsubscribe() {
                    this.isProcessing = true;
                    this.pushSupportError = '';

                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();

                        if (subscription) {
                            await fetch('/push-subscriptions', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ endpoint: subscription.endpoint }),
                            });

                            await subscription.unsubscribe();
                            this.isPushEnabled = false;
                        }
                    } catch (error) {
                        console.error('Error unsubscribing:', error);
                        this.pushSupportError = 'فشل إلغاء الاشتراك.';
                    } finally {
                        this.isProcessing = false;
                    }
                }
            }));
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const registerButton = document.getElementById('register-webauthn-device');

        if (registerButton) {
            registerButton.addEventListener('click', async () => {
                try {
                    const options = await window.axios.post('{{ route("webauthn.register.options") }}');
                    const credential = await WebAuthn.create(options.data); // هذا سيعمل الآن
                    const verification = await window.axios.post('{{ route("webauthn.register.verify") }}', credential);

                    if (verification.data.verified) {
                        alert('تم تسجيل الجهاز بنجاح!');
                        location.reload();
                    } else {
                        alert('فشل تسجيل الجهاز.');
                    }
                } catch (error) {
                    console.error("فشل التسجيل:", error);
                    const errorMessage = error.response ? (error.response.data.message || 'خطأ من الخادم') : error.message;
                    alert('حدث خطأ أثناء تسجيل الجهاز: ' + errorMessage);
                }
            });
        }
    });
</script>
    @endpush
    {{-- ====================================================================== --}}
    {{-- ======================= END: JAVASCRIPT SECTION ====================== --}}
    {{-- ====================================================================== --}}

</x-app-layout>