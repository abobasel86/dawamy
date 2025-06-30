<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight header-title">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <!-- Alpine.js component for camera modal -->
    <div x-data="cameraApp"
         data-login-options="{{ route('webauthn.login.options') }}"
         data-punchin="{{ route('attendance.punchin') }}"
         data-punchout="{{ route('attendance.punchout') }}"
         data-dashboard="{{ route('dashboard') }}">
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

    </div>


</x-app-layout>
