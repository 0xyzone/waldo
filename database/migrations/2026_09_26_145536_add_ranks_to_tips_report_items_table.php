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
            $table->integer('department_rank')->default(999)->after('department')->index();
            $table->integer('designation_rank')->default(999)->after('designation')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_report_items', function (Blueprint $table) {
            $table->dropColumn(['department_rank', 'designation_rank']);
        });
    }
};
