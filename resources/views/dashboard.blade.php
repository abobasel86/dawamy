<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <!-- Alpine.js component for camera modal -->
    <div x-data="cameraApp()">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 bg-white border-b border-gray-200 text-center">
                        <h3 class="text-3xl font-bold mb-2" style="color: #156b68;">مرحباً بك، {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600 mb-6">نتمنى لك يوماً سعيداً ومنتجاً</p>

                        @if (session('success'))<div class="mb-4 font-medium text-sm text-green-800 bg-green-100 p-3 rounded-md">{{ session('success') }}</div>@endif
                        @if (session('error'))<div class="mb-4 font-medium text-sm text-red-800 bg-red-100 p-3 rounded-md">{{ session('error') }}</div>@endif

                        <!-- Action Buttons -->
                        <div id="action-buttons">
                            @if ($hasPunchedIn)
                                <button @click="openCamera('out')" type="button" class="btn-secondary text-lg">تسجيل انصراف</button>
                            @else
                                <button @click="openCamera('in')" type="button" class="btn-primary text-lg">تسجيل حضور</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera Modal -->
        <div x-show="showModal" 
     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" 
     style="display: none;">

    <div class="bg-white rounded-lg shadow-xl flex flex-col" 
         style="width: 100%; max-width: 500px; max-height: 90vh;" 
         @click.away="closeCamera()">

        <div class="p-4 border-b">
            <h3 class="text-xl font-bold text-center">التحقق بالصورة الشخصية</h3>
        </div>

        <div class="p-6 overflow-y-auto">
            <p class="text-gray-600 mb-4 text-center">يرجى النظر مباشرة إلى الكاميرا والضغط على زر الالتقاط.</p>

            <div class="relative w-full aspect-square bg-gray-200 rounded-md overflow-hidden mx-auto max-w-xs">
                <video x-ref="video" class="w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
            </div>

            <div id="geo-error-message" style="display: none;" class="mt-4 font-medium text-sm text-red-800 bg-red-100 p-3 rounded-md"></div>
            <div id="loading-spinner" style="display: none;" class="mt-4"><p class="text-blue-600 text-center">جاري التحقق من الموقع...</p></div>
        </div>

        <div class="p-4 bg-gray-50 border-t mt-auto">
            <div class="flex justify-center space-x-4 space-x-reverse">
                <button @click="closeCamera()" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">إلغاء</button>
                <button @click="captureAndSubmit()" type="button" class="btn-primary py-2 px-6">التقاط وتأكيد</button>
            </div>
        </div>
    </div>
</div>

        <!-- Hidden Forms -->
        <form id="punchInForm" method="POST" action="{{ route('attendance.punchin') }}" class="hidden">@csrf<input type="hidden" name="latitude" id="latitude_in"><input type="hidden" name="longitude" id="longitude_in"><input type="hidden" name="selfie_image" id="selfie_image_in"><input type="hidden" name="credential_id" id="credential_id_in"></form>
        <form id="punchOutForm" method="POST" action="{{ route('attendance.punchout') }}" class="hidden">@csrf<input type="hidden" name="latitude" id="latitude_out"><input type="hidden" name="longitude" id="longitude_out"><input type="hidden" name="selfie_image" id="selfie_image_out"><input type="hidden" name="credential_id" id="credential_id_out"></form>
    </div>

    @push('scripts')
<script>
    const registeredCreds = @json($credentialIds);
    const loginChallengeBase64 = @json($challenge);
    const loginChallenge = Uint8Array.from(atob(loginChallengeBase64), c => c.charCodeAt(0));

    function cameraApp() {
        return {
            showModal: false,
            stream: null,
            actionType: '',

            openCamera(type) {
                this.actionType = type;
                this.showModal = true;
                
                this.$nextTick(() => {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            this.stream = stream;
                            const videoElement = this.$refs.video;

                            // ===== هذا هو الجزء الأهم الذي يحل المشكلة =====
                            // نتأكد من أن عنصر الفيديو موجود وجاهز قبل استخدامه
                            if (videoElement) {
                                videoElement.srcObject = stream;
                            } else {
                                // إذا لم يكن جاهزاً، نظهر رسالة خطأ ونغلق النافذة
                                console.error("AlpineJS could not find the x-ref='video' element in time.");
                                alert("حدث خطأ في تهيئة الكاميرا. الرجاء المحاولة مرة أخرى.");
                                this.closeCamera();
                            }
                            // ============================================

                        })
                        .catch(err => {
                            console.error("Error accessing camera: ", err);
                            if(err.name === "NotAllowedError") {
                                alert("لقد قمت برفض إذن استخدام الكاميرا. لا يمكن تسجيل الحضور بدونها.");
                            } else {
                                alert("لا يمكن الوصول إلى الكاميرا. تأكد من عدم استخدامها في تطبيق آخر.");
                            }
                            this.closeCamera();
                        });
                });
            },

            // --- باقي الدوال تبقى كما هي ---
            closeCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }
                this.showModal = false;
            },

            captureAndSubmit() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = canvas.toDataURL('image/png');
                
                document.getElementById('selfie_image_' + this.actionType).value = imageData;
                this.requestCredential();
            },

            requestCredential() {
                if (!window.PublicKeyCredential) {
                    alert('جهازك لا يدعم التحقق البيومتري');
                    return;
                }

                navigator.credentials.get({
                    publicKey: {
                        challenge: loginChallenge,
                        userVerification: 'required',
                        allowCredentials: registeredCreds.map(id => ({
                            type: 'public-key',
                            id: Uint8Array.from(atob(id), c => c.charCodeAt(0)),
                            transports: ['internal'],
                            authenticatorAttachment: 'platform'
                        }))
                    }
                })
                    .then(cred => {
                        const rawId = btoa(String.fromCharCode(...new Uint8Array(cred.rawId)));
                        document.getElementById('credential_id_' + this.actionType).value = rawId;
                        this.getLocation();
                    })
                    .catch(() => {
                        alert('فشل التحقق البيومتري');
                        this.closeCamera();
                    });
            },

            getLocation() {
                const form = document.getElementById('punch' + (this.actionType.charAt(0).toUpperCase() + this.actionType.slice(1)) + 'Form');
                const latInput = document.getElementById('latitude_' + this.actionType);
                const lonInput = document.getElementById('longitude_' + this.actionType);
                
                document.getElementById('loading-spinner').style.display = 'block';

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latInput.value = position.coords.latitude;
                        lonInput.value = position.coords.longitude;
                        form.submit();
                    },
                    (err) => {
                        document.getElementById('loading-spinner').style.display = 'none';
                        const errorMsg = 'فشل تحديد الموقع. يرجى التأكد من تفعيل خدمات الموقع والموافقة على الإذن.';
                        document.getElementById('geo-error-message').innerText = errorMsg;
                        document.getElementById('geo-error-message').style.display = 'block';
                         alert(errorMsg + ` السبب: ${err.message}`);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        }
    }
</script>
@endpush
</x-app-layout>
