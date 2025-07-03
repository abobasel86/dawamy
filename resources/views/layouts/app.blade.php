<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dawamy') }}</title>

    {{-- ============= START: PWA & MOBILE CONFIG ============= --}}
    <meta name="theme-color" content="#156b68">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="دوامي">
    <link rel="apple-touch-icon" href="/images/icons/apple-192x192.png">
    {{-- ============== END: PWA & MOBILE CONFIG ============== --}}

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #156b68;
            --secondary-color: #caa453;
            --primary-hover: #10524f;
            --text-color: #333;
        }
        body {
            /* font-family: 'Almarai', sans-serif; */
            color: var(--text-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #b9944a;
        }
        .nav-link-custom.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            font-weight: 700;
        }
        .header-title {
            color: var(--primary-color);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col bg-gray-100">
        @include('layouts.navigation')

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="flex-grow">
            {{ $slot }}
        </main>
    </div>

    {{-- ================================================================= --}}
    {{-- ============= START: ALL REQUIRED SCRIPTS AT THE END ============ --}}
    {{-- ================================================================= --}}

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // تعريف دالة تسجيل الخروج الذكية
        const handleSmartLogout = async function (event) {
            event.preventDefault();
            const form = event.target; // الحصول على النموذج الذي تم الضغط عليه

            if (!('serviceWorker' in navigator && 'PushManager' in window)) {
                form.submit(); // خروج مباشر إذا كانت الإشعارات غير مدعومة
                return;
            }

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    console.log('Unsubscribing before logout...');
                    // إرسال طلب الحذف للخادم
                    await fetch('/push-subscriptions/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ endpoint: subscription.endpoint }),
                    });
                    // إلغاء الاشتراك من المتصفح
                    await subscription.unsubscribe();
                }
            } catch (error) {
                console.error('Could not unsubscribe, but proceeding with logout.', error);
            } finally {
                // إتمام عملية تسجيل الخروج في كل الأحوال
                form.submit();
            }
        };

        // === START: التصحيح هنا ===
        // استهداف النموذجين وتطبيق نفس الدالة عليهما
        const desktopLogoutForm = document.getElementById('logout-form');
        const mobileLogoutForm = document.getElementById('logout-form-mobile');

        if (desktopLogoutForm) {
            desktopLogoutForm.addEventListener('submit', handleSmartLogout);
        }
        if (mobileLogoutForm) {
            mobileLogoutForm.addEventListener('submit', handleSmartLogout);
        }
        // === END: التصحيح هنا ===
    });
</script>

    <script>
        function notificationsComponent() {
            return {
                isOpen: false,
                totalUnread: 0,
                latestNotifications: [],

                init() {
                    this.fetchSummary();
                    // تحديث الإشعارات كل دقيقة
                    setInterval(() => {
                        this.fetchSummary();
                    }, 60000);
                },

                fetchSummary() {
                    fetch('{{ route("notifications.summary") }}')
                        .then(response => response.json())
                        .then(data => {
                            this.totalUnread = data.total_unread;
                            this.latestNotifications = data.latest_notifications;
                        })
                        .catch(error => console.error('Error fetching notifications summary:', error));
                },

                read(notification) {
                    // If the notification is unread, update it on the frontend and backend
                    if (!notification.read_at) {
                        this.totalUnread = Math.max(0, this.totalUnread - 1);
                        
                        let targetNotification = this.latestNotifications.find(n => n.id === notification.id);
                        if (targetNotification) {
                            // This line updates the UI immediately
                            targetNotification.read_at = new Date().toISOString();
                        }

                        // Send a request to the server to mark the notification as read in the database
                        fetch(`/notifications/${notification.id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'ngrok-skip-browser-warning': 'true' //  <-- The fix is here
                            }
                        }).catch(err => console.error('Failed to mark notification as read:', err));
                    }
                    
                    // Check for a URL and redirect after a short delay
                    // استخلاص رسالة الإشعار
                    const message = notification.data.message || '';

                    // 🎯 الشرط: إذا كان هناك رابط والإشعار لا يحتوي على كلمة "تفويضك"
                    if (notification.data.url && !message.includes('تفويضك')) {
                        // إعادة التوجيه بعد تأخير بسيط للسماح للواجهة بالتحديث
                        setTimeout(() => {
                            window.location.href = notification.data.url;
                        }, 100);
                    }
                    // إذا كان الإشعار يخص التفويض، لن يتم عمل أي شيء آخر (لن يتم التوجيه)
                }
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
    @stack('scripts')
</body>
</html>