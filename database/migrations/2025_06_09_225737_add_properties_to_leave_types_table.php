<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // لتحديد ما إذا كانت الإجازة بالساعة أم باليوم
            $table->enum('unit', ['days', 'hours'])->default('days')->after('days_annually');
            // لتحديد ما إذا كانت تتطلب إرفاق ملف
            $table->boolean('requires_attachment')->default(false)->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['unit', 'requires_attachment']);
        });
    }
};