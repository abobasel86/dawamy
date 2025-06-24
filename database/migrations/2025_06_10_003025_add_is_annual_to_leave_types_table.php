<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // هذا الحقل سيميز الإجازة السنوية الرئيسية التي تنطبق عليها سياسة الرصيد التراكمي
            $table->boolean('is_annual')->default(false)->after('requires_attachment');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('is_annual');
        });
    }
};