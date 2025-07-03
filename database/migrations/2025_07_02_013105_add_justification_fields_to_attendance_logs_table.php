<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->integer('lateness_minutes')->nullable()->after('punch_out_user_agent');
            $table->text('justification')->nullable()->after('lateness_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['lateness_minutes', 'justification']);
        });
    }
};