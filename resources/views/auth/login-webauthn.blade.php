<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">تسجيل الدخول</h2>
        <p class="text-gray-600">استخدم بصمتك أو معرف الوجه للدخول بسرعة.</p>
    </div>

    <div id="status" class="mb-4 text-center font-medium text-sm text-red-600"></div>

    <div class="mt-4 flex flex-col items-center">
        <button id="login-with-passkey" class="btn-primary w-full max-w-xs text-lg">
            تسجيل الدخول بالبصمة
        </button>
        <a href="{{ route('login') }}" class="mt-4 underline text-sm text-gray-600 hover:text-gray-900">
            أو استخدم كلمة المرور
        </a>
    </div>

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
                            // تمت المصادقة بنجاح، سيتم توجيه المستخدم تلقائيًا من الخادم
                            window.location.href = "{{ route('dashboard') }}";
                        } else {
                            statusDiv.innerText = `فشلت المصادقة: ${error.message}`;
                        }

                    } catch (e) {
                        console.error("فشل تسجيل الدخول:", e);
                        statusDiv.innerText = 'حدث خطأ غير متوقع.';
                    } finally {
                        loginButton.disabled = false;
                        loginButton.innerText = 'تسجيل الدخول بالبصمة';
                    }
                });
            }
        });
    </script>
    @endpush
</x-guest-layout>