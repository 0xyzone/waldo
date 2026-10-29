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
        Schema::table('designations', function (Blueprint $table) {
            $table->decimal('royalty_annual_budget', 12, 2)->default(0)->after('default_tips_amount');
            $table->decimal('royalty_monthly_max', 12, 2)->default(0)->after('royalty_annual_budget');
            $table->decimal('royalty_monthly_min', 12, 2)->default(0)->after('royalty_monthly_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->dropColumn([
                'royalty_annual_budget',
                'royalty_monthly_max',
                'royalty_monthly_min',
            ]);
        });
    }
};
