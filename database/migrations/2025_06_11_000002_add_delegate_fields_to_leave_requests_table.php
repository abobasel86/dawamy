
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('delegate_user_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->enum('delegate_status', ['pending', 'approved', 'rejected'])->default('pending')->after('delegate_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['delegate_user_id']);
            $table->dropColumn(['delegate_user_id', 'delegate_status']);
        });
    }
};
