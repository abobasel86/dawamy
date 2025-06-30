<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('الأجهزة الموثوقة') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('قم بإدارة الأجهزة المرتبطة بحسابك لإستخدام تسجيل الدخول بالبصمة.') }}
        </p>
    </header>

    <ul class="mt-6 space-y-2">
        @forelse ($credentials as $cred)
            <li class="flex items-center justify-between bg-gray-50 p-2 rounded">
                <span>{{ $cred->alias ?? $cred->id }}</span>
                <form method="POST" action="{{ route('passkeys.destroy', $cred) }}" onsubmit="return confirm('هل أنت متأكد من الحذف؟');" class="ml-2">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('حذف') }}</x-danger-button>
                </form>
            </li>
        @empty
            <li class="text-sm text-gray-600">{{ __('لا توجد أجهزة مسجلة.') }}</li>
        @endforelse
    </ul>

    <div class="mt-6">
        <x-primary-button type="button" id="add-passkey">{{ __('إضافة جهاز جديد') }}</x-primary-button>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('add-passkey');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
                alert('متصفحك لا يدعم هذه الميزة أو فشل تحميل المكتبة.');
                return;
            }
            btn.disabled = true;
            btn.innerText = 'جاري الإضافة...';
            try {
                const { success, error } = await Webpass.attest(
                    "{{ route('webauthn.register.options') }}",
                    "{{ route('webauthn.register') }}"
                );
                if (success) {
                    window.location.reload();
                } else {
                    alert('فشلت العملية: ' + error.message);
                }
            } catch (e) {
                console.error(e);
                alert('حدث خطأ غير متوقع.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'إضافة جهاز جديد';
            }
        });
    });
</script>
@endpush
