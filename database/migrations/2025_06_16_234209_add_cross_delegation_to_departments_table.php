<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // هذا الحقل يحدد ما إذا كان مسموحاً لموظفي القسم بالتفويض خارج قسمهم
            $table->boolean('allow_cross_delegation')->default(false)->after('requires_assistant_approval');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('allow_cross_delegation');
        });
    }
};