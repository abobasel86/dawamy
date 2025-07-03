<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // أولاً: تعديل جدول طلبات العمل الإضافي لإضافة الحقول اللازمة للتسلسل
        Schema::table('overtime_requests', function (Blueprint $table) {
            // تعديل حقل الحالة ليصبح أكثر مرونة
            // ملاحظة: هذا الأمر يتطلب حزمة "doctrine/dbal". إذا لم تكن مثبتة، نفذ الأمر التالي:
            // composer require doctrine/dbal
            $table->string('status')->default('pending')->change();

            // إضافة حقول تسلسل الموافقات بعد حقل الحالة مباشرة
            $table->integer('approval_level')->default(1)->after('status');
            $table->foreignId('current_approver_id')->nullable()->after('approval_level')->constrained('users')->onDelete('set null');
        });

        // ثانياً: إنشاء جدول جديد لتتبع سجل الموافقات بالكامل
        Schema::create('overtime_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->string('status'); // e.g., 'approved_level_1', 'rejected', 'forwarded'
            $table->text('remarks')->nullable(); // لحفظ سبب الرفض
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_approval_histories');

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropForeign(['current_approver_id']);
            $table->dropColumn(['approval_level', 'current_approver_id']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};