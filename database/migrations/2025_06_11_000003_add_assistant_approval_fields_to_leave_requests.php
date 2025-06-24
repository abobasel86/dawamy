
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('assistant_approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->text('assistant_approval_comment')->nullable()->after('assistant_approval_status');
            $table->foreignId('assistant_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('assistant_approval_comment');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['assistant_approved_by']);
            $table->dropColumn(['assistant_approval_status', 'assistant_approval_comment', 'assistant_approved_by']);
        });
    }
};
