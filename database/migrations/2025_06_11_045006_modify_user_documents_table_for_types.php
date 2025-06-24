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
        Schema::table('user_documents', function (Blueprint $table) {
            // أولاً، نحذف العمود القديم الذي كان يخزن النوع كنص
            $table->dropColumn('document_type');
            
            // ثانياً، نضيف العمود الجديد الذي سيكون مفتاحاً خارجياً
            $table->foreignId('document_type_id')->after('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
            $table->string('document_type'); // إعادة العمود القديم عند التراجع
        });
    }
};
