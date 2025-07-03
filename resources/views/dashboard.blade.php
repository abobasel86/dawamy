<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <div x-data="attendanceApp()">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 bg-white border-b border-gray-200 text-center">
                        <h3 class="text-3xl font-bold mb-2" style="color: #156b68;">مرحباً بك، {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600 mb-6">نتمنى لك يوماً سعيداً ومنتجاً</p>

                        @if (session('success'))<div class="mb-4 font-medium text-sm text-green-800 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>@endif
                        @if (session('error'))<div class="mb-4 font-medium text-sm text-red-800 bg-red-100 p-3 rounded-md" id="error-message">{{ session('error') }}</div>@endif

                        <div id="action-buttons">
                            @if ($hasPunchedIn)
                                <button @click="initiateAction('out')" type="button" class="btn-secondary text-lg" :disabled="isProcessing">
                                    <span x-show="!isProcessing">تسجيل انصراف</span>
                                    <span x-show="isProcessing">جاري المعالجة...</span>
                                </button>
                            @else
                                <button @click="initiateAction('in')" type="button" class="btn-primary text-lg" :disabled="isProcessing">
                                    <span x-show="!isProcessing">تسجيل حضور</span>
                                    <span x-show="isProcessing">جاري المعالجة...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showModal"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4"
             style="display: none;">
            <div @click.away="closeCamera()" class="bg-white rounded-lg shadow-xl flex flex-col" style="width: 100%; max-width: 500px; max-height: 90vh;">
                <div class="p-4 border-b">
                    <h3 class="text-xl font-bold text-center">التحقق بالصورة الشخصية</h3>
                </div>
                <div class="p-6 overflow-y-auto">
                    <p class="text-gray-600 mb-4 text-center">يرجى النظر مباشرة إلى الكاميرا والضغط على زر الالتقاط.</p>
                    <div class="relative w-full aspect-square bg-gray-200 rounded-md overflow-hidden mx-auto max-w-xs">
                        <video x-ref="video" class="w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 border-t mt-auto">
                    <div class="flex justify-center space-x-4 space-x-reverse">
                        <button @click="closeCamera()" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">إلغاء</button>
                        <button @click="captureImage()" type="button" class="btn-primary py-2 px-6">التقاط</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="punchInForm" method="POST" action="{{ route('attendance.punchin') }}" class="hidden">@csrf<input type="hidden" name="latitude" id="latitude_in"><input type="hidden" name="longitude" id="longitude_in"><input type="hidden" name="selfie_image" id="selfie_image_in"></form>
        <form id="punchOutForm" method="POST" action="{{ route('attendance.punchout') }}" class="hidden">@csrf<input type="hidden" name="latitude" id="latitude_out"><input type="hidden" name="longitude" id="longitude_out"><input type="hidden" name="selfie_image" id="selfie_image_out"></form>
    </div>

    @push('scripts')
    <script>
        // --- START: دوال مساعدة لمعالجة بيانات WebAuthn ---
        function base64UrlToBuffer(base64Url) {
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray.buffer;
        }

        function bufferToBase64Url(buffer) {
            return btoa(String.fromCharCode(...new Uint8Array(buffer)))
                .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }
        // --- END: دوال مساعدة ---

        function attendanceApp() {
            return {
                showModal: false,
                isProcessing: false,
                stream: null,
                actionType: '',

                initiateAction(type) {
                    this.actionType = type;
                    this.isProcessing = true;
                    this.getLocation().then(position => {
                        this.openCamera(position);
                    }).catch(error => {
                        alert(error);
                        this.isProcessing = false;
                    });
                },

                getLocation() {
                    return new Promise((resolve, reject) => {
                        if (!navigator.geolocation) {
                            reject('خدمات تحديد الموقع غير مدعومة في متصفحك.');
                            return;
                        }
                        navigator.geolocation.getCurrentPosition(
                            (position) => resolve(position),
                            (err) => reject(`فشل تحديد الموقع: ${err.message}`),
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    });
                },

                openCamera(position) {
                    const form = document.getElementById('punch' + (this.actionType.charAt(0).toUpperCase() + this.actionType.slice(1)) + 'Form');
                    form.elements['latitude'].value = position.coords.latitude;
                    form.elements['longitude'].value = position.coords.longitude;
                    this.showModal = true;
                    this.$nextTick(() => {
                        navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
                            this.stream = stream;
                            this.$refs.video.srcObject = stream;
                        }).catch(err => {
                            alert("لا يمكن الوصول إلى الكاميرا. يرجى التأكد من منح الإذن اللازم.");
                            this.closeCamera();
                        });
                    });
                },

                captureImage() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    document.getElementById('selfie_image_' + this.actionType).value = canvas.toDataURL('image/png');
                    this.closeCamera(false);
                    this.authenticateAndSubmit();
                },

                async authenticateAndSubmit() {
                    try {
                        // 1. جلب خيارات التحقق من الخادم
                        const response = await fetch("{{ route('webauthn.login.options') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ email: '{{ Auth::user()->email }}' })
                        });
                        if (!response.ok) throw new Error('لا يمكن جلب خيارات التحقق من الخادم.');
                        const options = await response.json();

                        // 2. تحويل البيانات لتكون متوافقة مع المتصفح
                        options.challenge = base64UrlToBuffer(options.challenge);
                        if (options.allowCredentials) {
                            options.allowCredentials.forEach(cred => cred.id = base64UrlToBuffer(cred.id));
                        }

                        // 3. طلب المصادقة من المتصفح (WebAuthn)
                        const assertion = await navigator.credentials.get({ publicKey: options });

                        // 4. تحويل بيانات المصادقة الناتجة لتكون متوافقة مع النموذج
                        const formFriendlyAssertion = {
                            id: assertion.id,
                            rawId: bufferToBase64Url(assertion.rawId),
                            type: assertion.type,
                            response: {
                                authenticatorData: bufferToBase64Url(assertion.response.authenticatorData),
                                clientDataJSON: bufferToBase64Url(assertion.response.clientDataJSON),
                                signature: bufferToBase64Url(assertion.response.signature),
                                userHandle: assertion.response.userHandle ? bufferToBase64Url(assertion.response.userHandle) : null,
                            },
                        };
                        
                        // 5. إضافة البيانات إلى النموذج وإرساله
                        const form = document.getElementById('punch' + (this.actionType.charAt(0).toUpperCase() + this.actionType.slice(1)) + 'Form');
                        
                        // إضافة بيانات المصادقة كحقول مخفية
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="id" value="${formFriendlyAssertion.id}">`);
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="rawId" value="${formFriendlyAssertion.rawId}">`);
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="type" value="${formFriendlyAssertion.type}">`);
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="response[authenticatorData]" value="${formFriendlyAssertion.response.authenticatorData}">`);
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="response[clientDataJSON]" value="${formFriendlyAssertion.response.clientDataJSON}">`);
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="response[signature]" value="${formFriendlyAssertion.response.signature}">`);
                        if(formFriendlyAssertion.response.userHandle) {
                            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="response[userHandle]" value="${formFriendlyAssertion.response.userHandle}">`);
                        }
                        // هذا الحقل هو الذي كان يسبب المشكلة الأساسية "فشل التحقق من بيانات الدخول"
                        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="credential_id" value="${formFriendlyAssertion.id}">`);

                        form.submit();

                    } catch (error) {
                        console.error('WebAuthn Authentication failed:', error);
                        alert(`فشلت عملية التحقق بالبصمة: ${error.message}`);
                        this.isProcessing = false;
                    }
                },
                
                closeCamera(stopProcessing = true) {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                    this.showModal = false;
                    if (stopProcessing) {
                       this.isProcessing = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>