import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // هذا هو الإعداد الكامل والصحيح للخادم
    server: {
        // تحديد المضيف ليعمل على نطاق dawamy.test
        host: 'dawamy.test', 
        
        // استخدام شهادات الأمان الصحيحة الخاصة بـ Laragon
        https: {
            key: fs.readFileSync(process.env.VITE_SSL_KEY || 'C:/laragon/etc/ssl/laragon.key'),
            cert: fs.readFileSync(process.env.VITE_SSL_CERT || 'C:/laragon/etc/ssl/laragon.crt'),
        },

        // ===== السطر الأهم لحل المشكلة الحالية =====
        cors: true,
        // ===========================================

        // تحديد المضيف لعملية التحديث التلقائي
        hmr: {
            host: 'dawamy.test',
        },
    }
});