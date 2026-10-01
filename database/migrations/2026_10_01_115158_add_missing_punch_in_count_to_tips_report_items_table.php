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
        Schema::table('tips_report_items', function (Blueprint $table) {
            $table->integer('missing_punch_in_count')->default(0)->after('early_out_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_report_items', function (Blueprint $table) {
            $table->dropColumn('missing_punch_in_count');
        });
    }
};
