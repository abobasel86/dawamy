<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This requires the 'doctrine/dbal' package.
        // Run: composer require doctrine/dbal
        Schema::table('overtime_requests', function (Blueprint $table) {
            // Change the column to be nullable
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            // Revert the change if needed
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
