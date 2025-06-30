<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dawamy') }}</title>

        {{-- سنستخدم نفس الخط المعتمد في التطبيق --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">


        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
        
        {{-- تطبيق الخط على الصفحة --}}
        <style>
            body {
                font-family: 'Almarai', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            
            {{-- ===== الجزء الجديد الذي تمت إضافته ===== --}}
            <div class="text-center mb-4">
                <a href="/">
                    {{-- تأكد من أن مسار الشعار صحيح --}}
                    <img src="{{ asset('images/logo.png') }}" alt="شعار الشركة" class="w-24 h-24 mx-auto">
                </a>

                <h1 style="color: #156b68;" class="text-3xl font-bold mt-2">
                    دوامي
                </h1>
            </div>
            {{-- ======================================= --}}

            <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
        @stack('scripts')
    </body>
</html>