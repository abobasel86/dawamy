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
        Schema::table('users', function (Blueprint $table) {
            // حالة الموظف الحالية
            $table->enum('employment_status', ['probation', 'permanent', 'contract'])->default('probation')->after('location_id');
            // تاريخ بدء العمل في الشركة
            $table->date('hire_date')->nullable()->after('employment_status');
            // تاريخ نهاية فترة الاختبار
            $table->date('probation_end_date')->nullable()->after('hire_date');
            // تاريخ التثبيت كموظف دائم
            $table->date('permanent_date')->nullable()->after('probation_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'hire_date', 'probation_end_date', 'permanent_date']);
        });
    }
};
