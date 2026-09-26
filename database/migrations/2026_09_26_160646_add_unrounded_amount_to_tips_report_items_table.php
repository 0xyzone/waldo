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
            $table->decimal('unrounded_amount', 12, 2)->nullable()->after('calculated_tips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_report_items', function (Blueprint $table) {
            $table->dropColumn('unrounded_amount');
        });
    }
};
