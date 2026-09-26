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
            $table->decimal('final_distribution_amount', 12, 2)->nullable()->change();
            $table->decimal('calculated_tips', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_report_items', function (Blueprint $table) {
            $table->decimal('final_distribution_amount', 12, 2)->default(0)->change();
            $table->decimal('calculated_tips', 12, 2)->default(0)->change();
        });
    }
};
