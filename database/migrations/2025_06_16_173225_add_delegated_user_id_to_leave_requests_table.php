<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // هذا الحقل سيخزن معرّف الموظف الذي تم تفويضه
            $table->foreignId('delegated_user_id')->nullable()->after('end_time')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['delegated_user_id']);
            $table->dropColumn('delegated_user_id');
        });
    }
};