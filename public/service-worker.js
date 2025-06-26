// الاستماع لحدث التثبيت لتخزين الملفات المهمة (Caching) - اختياري لكن موصى به
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    // يمكنك إضافة منطق الـ Caching هنا لاحقاً لتحسين الأداء
});

// الاستماع لحدث التفعيل لتنظيف الـ Cache القديم
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
});

// أهم جزء: الاستماع لوصول إشعار من الخادم
self.addEventListener('push', function (event) {
    console.log('Service Worker: Push Received.');

    // إذا لم تصل بيانات مع الإشعار، لا تفعل شيئاً
    if (!event.data) {
        return;
    }

    const data = event.data.json();
    const title = data.title || 'Dawamy App';
    const options = {
        body: data.body,
        icon: data.icon || '/images/icons/icon-192x192.png', // أيقونة الإشعار
        badge: data.badge || '/images/icons/icon-192x192.png', // أيقونة شريط الحالة
        data: {
            url: data.url || '/' // الرابط الذي سيفتح عند الضغط على الإشعار
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// الاستماع لحدث الضغط على الإشعار
self.addEventListener('notificationclick', function (event) {
    // إغلاق الإشعار عند الضغط عليه
    event.notification.close();

    // فتح الرابط المرفق مع الإشعار في نافذة جديدة
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});