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
            $table->string('department_error_image_path')->nullable()->after('excel_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_reports', function (Blueprint $table) {
            $table->dropColumn('department_error_image_path');
        });
    }
};
