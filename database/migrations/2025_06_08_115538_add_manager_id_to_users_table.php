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
        Schema::table('users', function (Blueprint $table) {
            // إضافة حقل المدير، وهو مفتاح خارجي يشير إلى نفس جدول المستخدمين
            // يمكن أن يكون فارغًا (للمديرين الكبار الذين ليس لهم مدير مباشر)
            // onDelete('set null') تعني أنه إذا تم حذف حساب المدير، سيصبح هذا الحقل فارغًا للموظف
            $table->foreignId('manager_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // عند التراجع، قم بإزالة المفتاح الخارجي ثم العمود
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};

?>