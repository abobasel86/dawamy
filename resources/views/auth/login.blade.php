<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div id="status" class="mb-4 text-center font-medium text-sm text-red-600"></div>

    <form id="password-login-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('كلمة المرور')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 shadow-sm focus:ring-indigo-500" name="remember" style="color: var(--primary-color);">
                <span class="ms-2 text-sm text-gray-600">{{ __('تذكرني') }}</span>
            </label>
        </div>

        <div class="flex flex-col space-y-4 mt-6">
            {{-- زر الدخول بالبصمة (نوعه button لمنع إرسال النموذج) --}}
            <button type="button" id="login-with-passkey" class="btn-secondary text-black">
                الدخول بالبصمة
            </button>

            {{-- زر تسجيل الدخول العادي --}}
            <button type="submit" class="btn-primary">
                تسجيل الدخول
            </button>
        </div>

        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('هل نسيت كلمة المرور؟') }}
                </a>
            </div>
        @endif
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginButton = document.getElementById('login-with-passkey');
            const statusDiv = document.getElementById('status');

            if (loginButton) {
                loginButton.addEventListener('click', async () => {
                    // نتأكد من أن المكتبة تم تحميلها
                    if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
                        return statusDiv.innerText = "متصفحك لا يدعم هذه الميزة أو فشل تحميل المكتبة.";
                    }

                    loginButton.disabled = true;
                    loginButton.innerText = 'يرجى المصادقة...';
                    statusDiv.innerText = '';

                    try {
                        const { success, error } = await Webpass.assert(
                            "{{ route('webauthn.login.options') }}",
                            "{{ route('webauthn.login') }}"
                        );

                        if (success) {
                            window.location.href = "{{ route('dashboard') }}";
                        } else {
                            statusDiv.innerText = `فشلت المصادقة: ${error.message}`;
                        }

                    } catch (e) {
                        console.error("فشل تسجيل الدخول:", e);
                        statusDiv.innerText = 'حدث خطأ غير متوقع.';
                    } finally {
                        loginButton.disabled = false;
                        loginButton.innerText = 'الدخول بالبصمة';
                    }
                });
            }
        });
    </script>
    @endpush
</x-guest-layout>