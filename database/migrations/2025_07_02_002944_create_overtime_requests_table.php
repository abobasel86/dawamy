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
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // الموظف صاحب الطلب
            $table->date('date'); // تاريخ العمل الإضافي
            $table->time('start_time'); // وقت بداية الإضافي
            $table->time('end_time'); // وقت نهاية الإضافي
            $table->integer('actual_minutes'); // الدقائق الفعلية التي عملها الموظف
            $table->decimal('payable_hours', 5, 2)->default(0); // الساعات المستحقة للدفع بعد الحساب
            $table->text('reason'); // سبب العمل الإضافي الذي أدخله الموظف
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة الطلب
            $table->foreignId('approved_by')->nullable()->constrained('users'); // من قام بالموافقة
            $table->text('rejection_reason')->nullable(); // سبب الرفض
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};