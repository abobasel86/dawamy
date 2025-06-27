<section class="space-y-6"
    x-data="{
        register() {
            if (!window.PublicKeyCredential) {
                alert('جهازك لا يدعم التحقق البيومتري');
                return;
            }

            const challenge = Uint8Array.from(atob('{{ $registerChallenge }}'), c => c.charCodeAt(0));

            navigator.credentials.create({
                publicKey: {
                    challenge: challenge,
                    authenticatorSelection: {
                        authenticatorAttachment: 'platform',
                        userVerification: 'required'
                    }
                }
            })
                .then(c => {
                    document.getElementById('webauthn_cred').value = btoa(
                        String.fromCharCode(...new Uint8Array(c.rawId))
                    );

                    const pk = c.response.getPublicKey();
                    if (pk) {
                        document.getElementById('webauthn_key').value = btoa(
                            String.fromCharCode(...new Uint8Array(pk))
                        );
                    }

                    document.getElementById('webauthnForm').submit();
                })
                .catch(() => alert('فشل تسجيل الجهاز'));
        }
    }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">تسجيل جهاز بيومتري</h2>
        <p class="mt-1 text-sm text-gray-600">سجّل جهازك البيومتري لاستخدامه في تسجيل الحضور.</p>
    </header>
    <button type="button" class="btn-primary" @click="register()">تسجيل الجهاز</button>
    <form id="webauthnForm" method="POST" action="{{ route('webauthn.register') }}" class="hidden">
        @csrf
        <input type="hidden" name="name" value="platform">
        <input type="hidden" name="credential_id" id="webauthn_cred">
        <input type="hidden" name="public_key" id="webauthn_key">
    </form>
</section>
