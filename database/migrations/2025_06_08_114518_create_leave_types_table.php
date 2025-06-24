<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // هذا الجدول سيحتوي على أنواع الإجازات المتاحة في النظام
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // مثال: "إجازة سنوية", "إجازة مرضية"
            $table->integer('days_annually')->default(0); // الرصيد السنوي لهذا النوع
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

?>