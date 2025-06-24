<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // هذا الحقل يحدد ما إذا كانت طلبات هذا القسم تمر عبر الأمين العام المساعد
            $table->boolean('requires_assistant_approval')->default(false)->after('manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('requires_assistant_approval');
        });
    }
};