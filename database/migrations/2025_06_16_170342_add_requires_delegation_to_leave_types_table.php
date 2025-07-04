<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // هذا الحقل يحدد ما إذا كانت الإجازة تتطلب تحديد موظف مفوض
            $table->boolean('requires_delegation')->default(false)->after('is_annual');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('requires_delegation');
        });
    }
};