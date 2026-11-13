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
            $table->text('additional_attachments')->nullable()->after('department_error_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_reports', function (Blueprint $table) {
            $table->dropColumn('additional_attachments');
        });
    }
};
