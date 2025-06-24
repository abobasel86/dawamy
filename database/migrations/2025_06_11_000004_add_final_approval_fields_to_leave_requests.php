
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('final_approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('assistant_approval_status');
            $table->text('final_approval_comment')->nullable()->after('final_approval_status');
            $table->foreignId('final_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('final_approval_comment');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['final_approved_by']);
            $table->dropColumn(['final_approval_status', 'final_approval_comment', 'final_approved_by']);
        });
    }
};
