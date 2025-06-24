<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // هذا الحقل يحدد ما إذا كان نوع الإجازة يجب أن يظهر في تقارير الأرصدة
            $table->boolean('show_in_balance')->default(true)->after('is_annual');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('show_in_balance');
        });
    }
};