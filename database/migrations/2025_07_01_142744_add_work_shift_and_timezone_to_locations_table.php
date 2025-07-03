<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('radius_meters');
            $table->foreignId('work_shift_id')->nullable()->after('timezone')->constrained()->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['work_shift_id']);
            $table->dropColumn(['timezone', 'work_shift_id']);
        });
    }
};