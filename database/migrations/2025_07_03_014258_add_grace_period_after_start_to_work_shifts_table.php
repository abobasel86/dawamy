<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('work_shifts', function (Blueprint $table) {
        // إضافة العمود الجديد بعد العمود الخاص بفترة السماح قبل الدوام
        $table->integer('grace_period_after_start_minutes')->default(0)->after('grace_period_before_start_minutes');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            //
        });
    }
};
