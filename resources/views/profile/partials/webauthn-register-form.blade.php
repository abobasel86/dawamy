<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">تسجيل جهاز بيومتري</h2>
        <p class="mt-1 text-sm text-gray-600">سجّل جهازك البيومتري لاستخدامه في تسجيل الحضور.</p>
    </header>

    <button type="button" id="register-webauthn-device" class="btn-primary">تسجيل الجهاز</button>
</section>

@push('scripts')
<script>
// =========================================================================
// START: الكود المصدري لمكتبة laragear/webauthn/client
// لقد قمت بنسخه هنا مباشرة لتجنب أي مشاكل في البناء
// =========================================================================
const WebAuthn = {
    prepareChallenge(challenge) {
        const buffer = new Uint8Array(challenge.length);
        for (let i = 0; i < challenge.length; i++) {
            buffer[i] = challenge.charCodeAt(i);
        }
        return buffer;
    },
    prepareUser(user) {
        if (user.id) {
            user.id = this.prepareChallenge(user.id);
        }
        return user;
    },
    prepareCredential(credential) {
        credential.id = this.prepareChallenge(credential.id);
        return credential;
    },
    prepareOptions(options) {
        if (options.challenge) {
            options.challenge = this.prepareChallenge(atob(options.challenge));
        }
        if (options.user) {
            options.user = this.prepareUser(options.user);
        }
        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map(this.prepareCredential.bind(this));
        }
        return options;
    },
    bufferEncode(buffer) {
        return btoa(String.fromCharCode(...new Uint8Array(buffer)))
            .replace(/\+/g, "-")
            .replace(/\//g, "_")
            .replace(/=/g, "");
    },
    credential(credential, type = 'attestation') {
        const response = {};
        for (const key in credential.response) {
            response[key] = credential.response[key] instanceof ArrayBuffer
                ? this.bufferEncode(credential.response[key])
                : credential.response[key];
        }
        return {
            id: credential.id,
            type: credential.type,
            rawId: this.bufferEncode(credential.rawId),
            response,
        };
    },
    async create(options) {
        return this.credential(await navigator.credentials.create({
            publicKey: this.prepareOptions(options),
        }));
    },
    async get(options) {
        return this.credential(await navigator.credentials.get({
            publicKey: this.prepareOptions(options),
        }), 'assertion');
    }
};
// =========================================================================
// END: الكود المصدري للمكتبة
// =========================================================================


// --- الكود الخاص بك الذي يستخدم المكتبة ---
document.addEventListener('DOMContentLoaded', () => {
    const registerButton = document.getElementById('register-webauthn-device');

    if (registerButton) {
        registerButton.addEventListener('click', async () => {
            try {
                // الخطوة 1: اطلب خيارات التسجيل من الخادم
                const optionsResponse = await window.axios.post('{{ route("webauthn.register.options") }}');
                
                // الخطوة 2: استدعاء دالة الإنشاء من الكود الذي أضفناه في الأعلى
                const credential = await WebAuthn.create(optionsResponse.data);
                
                // الخطوة 3: أرسل بيانات الاعتماد للتحقق منها
                const verificationResponse = await window.axios.post('{{ route("webauthn.register.verify") }}', credential);

                if (verificationResponse.data.verified) {
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