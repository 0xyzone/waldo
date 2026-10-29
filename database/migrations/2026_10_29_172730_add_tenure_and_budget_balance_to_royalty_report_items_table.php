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
            $table->string('join_date')->nullable()->after('employee_status');
            $table->unsignedSmallInteger('tenure_year')->nullable()->after('join_date');
            $table->date('tenure_start')->nullable()->after('tenure_year');
            $table->date('tenure_end')->nullable()->after('tenure_start');
            $table->unsignedTinyInteger('tenure_month')->nullable()->after('tenure_end');
            $table->decimal('starting_tenure_budget', 12, 2)->default(0)->after('annual_budget');
            $table->decimal('consumed_tenure_budget', 12, 2)->default(0)->after('final_payout');
            $table->decimal('remaining_budget', 12, 2)->default(0)->after('consumed_tenure_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('royalty_report_items', function (Blueprint $table) {
            $table->dropColumn([
                'join_date',
                'tenure_year',
                'tenure_start',
                'tenure_end',
                'tenure_month',
                'starting_tenure_budget',
                'consumed_tenure_budget',
                'remaining_budget',
            ]);
        });
    }
};
