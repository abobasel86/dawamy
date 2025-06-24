<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('punch_in_selfie_path')->nullable()->after('punch_in_ip_address');
            $table->text('punch_in_user_agent')->nullable()->after('punch_in_selfie_path');
            $table->string('punch_out_selfie_path')->nullable()->after('punch_out_ip_address');
            $table->text('punch_out_user_agent')->nullable()->after('punch_out_selfie_path');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn([
                'punch_in_selfie_path', 
                'punch_in_user_agent',
                'punch_out_selfie_path',
                'punch_out_user_agent'
            ]);
        });
    }
};