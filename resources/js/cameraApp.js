document.addEventListener('alpine:init', () => {
    Alpine.data('cameraApp', () => ({
        showModal: false,
        stream: null,
        actionType: '',
        selfie: null,
        webpass: Webpass.create({ findCsrfToken: true }),

        openCamera(type) {
            this.actionType = type;
            this.showModal = true;

            this.$nextTick(() => {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        this.stream = stream;
                        const videoElement = this.$refs.video;
                        if (videoElement) {
                            videoElement.srcObject = stream;
                        } else {
                            console.error("AlpineJS could not find the x-ref='video' element in time.");
                            alert("حدث خطأ في تهيئة الكاميرا. الرجاء المحاولة مرة أخرى.");
                            this.closeCamera();
                        }
                    })
                    .catch(err => {
                        console.error("Error accessing camera: ", err);
                        if (err.name === "NotAllowedError") {
                            alert("لقد قمت برفض إذن استخدام الكاميرا. لا يمكن تسجيل الحضور بدونها.");
                        } else {
                            alert("لا يمكن الوصول إلى الكاميرا. تأكد من عدم استخدامها في تطبيق آخر.");
                        }
                        this.closeCamera();
                    });
            });
        },

        closeCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
            this.showModal = false;
        },

        async captureAndSubmit() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = canvas.toDataURL('image/png');

            this.selfie = imageData;
            await this.getLocation();
        },

        async getLocation() {
            document.getElementById('loading-spinner').style.display = 'block';

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    await this.submitWithPasskey(position.coords.latitude, position.coords.longitude);
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
        },

        async submitWithPasskey(lat, lon) {
            try {
                const { success } = await this.webpass.assert(
                    window.cameraAppConfig.loginOptionsUrl,
                    {
                        path: this.actionType === 'in' ? window.cameraAppConfig.punchInUrl : window.cameraAppConfig.punchOutUrl,
                        body: {
                            latitude: lat,
                            longitude: lon,
                            selfie_image: this.selfie,
                        },
                    }
                );

                if (success) {
                    window.location.href = window.cameraAppConfig.dashboardUrl;
                } else {
                    alert('فشل التحقق من بيانات الدخول.');
                }
            } catch (e) {
                console.error(e);
                alert('فشل التحقق من بيانات الدخول.');
            } finally {
                document.getElementById('loading-spinner').style.display = 'none';
                this.closeCamera();
            }
        }
    }));
});
