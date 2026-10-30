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
        Schema::table('royalty_report_items', function (Blueprint $table) {
            if (! Schema::hasColumn('royalty_report_items', 'suspension_count')) {
                $table->unsignedInteger('suspension_count')->default(0)->after('missing_thumb_count');
            }
            if (! Schema::hasColumn('royalty_report_items', 'suspension_deduction_amount')) {
                $table->decimal('suspension_deduction_amount', 12, 2)->default(0)->after('attendance_deduction_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('royalty_report_items', function (Blueprint $table) {
            if (Schema::hasColumn('royalty_report_items', 'suspension_deduction_amount')) {
                $table->dropColumn('suspension_deduction_amount');
            }
            if (Schema::hasColumn('royalty_report_items', 'suspension_count')) {
                $table->dropColumn('suspension_count');
            }
        });
    }
};
