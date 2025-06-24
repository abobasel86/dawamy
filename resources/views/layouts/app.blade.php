<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-g">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dawamy') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <!-- تضمين خط المراعي من جوجل -->
        <!--<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">-->
		<link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#156b68">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="دوامي">
		<link rel="apple-touch-icon" href="/images/icons/apple-192x192.png">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
		
        <!-- ======================================================= -->
        <!--           === CSS مخصص للتصميم الجديد ===              -->
        <!-- ======================================================= -->
        <style>
            /* تعريف متغيرات الألوان لسهولة الاستخدام */
            :root {
                --primary-color: #156b68; /* اللون الأساسي - الأخضر */
                --secondary-color: #caa453; /* اللون الثانوي - الذهبي */
                --primary-hover: #10524f; /* لون عند مرور الماوس */
                --text-color: #333;
            }
			

	
            /* تطبيق خط المراعي على كامل جسم الصفحة */
            body {
                font-family: 'Almarai', sans-serif;
                color: var(--text-color);
            }
            
            /* تنسيق الأزرار الأساسية */
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

            /* تنسيق خاص بالروابط في القائمة العلوية */
            .nav-link-custom.active {
                color: var(--primary-color);
                border-bottom: 2px solid var(--primary-color);
                font-weight: 700;
            }

            /* تنسيق العناوين الرئيسية للصفحات */
            .header-title {
                color: var(--primary-color);
            }
        </style>

    </head>
    <body class="font-sans antialiased">
    {{-- تم إضافة flex flex-col هنا --}}
    <div class="min-h-screen flex flex-col bg-gray-100">
        @include('layouts.navigation')

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        {{-- تم إضافة flex-grow هنا --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>
    </div>
        
        @stack('scripts')
    <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
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
		// public/js/enable-push.js or inside a script tag in app.blade.php

const vapidPublicKey = '{{ config('webpush.vapid.public_key') }}';

const pushButton = document.getElementById('enable-push-btn');

if (pushButton) {
    pushButton.addEventListener('click', function() {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            navigator.serviceWorker.ready.then(function(registration) {
                subscribeUser(registration);
            });
        } else {
            alert('عذراً، الإشعارات غير مدعومة في هذا المتصفح.');
        }
    });
}

function subscribeUser(registration) {
    const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
    registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })
    .then(function(subscription) {
        console.log('User is subscribed.');
        // إرسال الاشتراك إلى السيرفر
        updateSubscriptionOnServer(subscription);
        alert('تم تفعيل الإشعارات بنجاح!');
    })
    .catch(function(err) {
        console.log('Failed to subscribe the user: ', err);
        alert('فشل تفعيل الإشعارات. يرجى التأكد من الموافقة على الأذونات.');
    });
}

function updateSubscriptionOnServer(subscription) {
    fetch('/push-subscriptions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(subscription)
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
		</script>
     </body>
</html>
