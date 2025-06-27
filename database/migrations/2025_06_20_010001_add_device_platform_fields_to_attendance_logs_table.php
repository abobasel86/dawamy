<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('punch_in_device')->nullable()->after('punch_in_user_agent');
            $table->string('punch_out_device')->nullable()->after('punch_out_user_agent');
            $table->string('punch_in_platform')->nullable()->after('punch_in_device');
            $table->string('punch_out_platform')->nullable()->after('punch_out_device');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn([
                'punch_in_device',
                'punch_out_device',
                'punch_in_platform',
                'punch_out_platform',
            ]);
        });
    }
};
