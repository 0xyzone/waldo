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
        Schema::table('tips_reports', function (Blueprint $table) {
            $table->string('cage_tips_report_image_path')->nullable()->after('excel_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_reports', function (Blueprint $table) {
            $table->dropColumn('cage_tips_report_image_path');
        });
    }
};
