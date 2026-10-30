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
        Schema::table('royalty_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('royalty_reports', 'notes')) {
                $table->text('notes')->nullable()->after('cutoff_date');
            }
            if (! Schema::hasColumn('royalty_reports', 'total_payout')) {
                $table->decimal('total_payout', 14, 2)->default(0)->after('status');
            }
            if (! Schema::hasColumn('royalty_reports', 'total_deductions')) {
                $table->decimal('total_deductions', 14, 2)->default(0)->after('total_payout');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('royalty_reports', function (Blueprint $table) {
            $columns = array_filter(
                ['notes', 'total_payout', 'total_deductions'],
                fn (string $col) => Schema::hasColumn('royalty_reports', $col)
            );
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
