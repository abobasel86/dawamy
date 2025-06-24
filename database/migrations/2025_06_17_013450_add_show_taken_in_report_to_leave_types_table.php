<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // هذا الحقل يحدد ما إذا كان يجب عرض عمود "الأيام المأخوذة" لهذا النوع في التقرير
            $table->boolean('show_taken_in_report')->default(true)->after('show_in_balance');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('show_taken_in_report');
        });
    }
};