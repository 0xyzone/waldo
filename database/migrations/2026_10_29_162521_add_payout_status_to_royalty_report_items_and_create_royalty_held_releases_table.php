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
            if (! Schema::hasColumn('royalty_report_items', 'payout_status')) {
                $table->string('payout_status')->default('Release')->after('final_payout'); // 'Release' or 'Hold'
            }
            if (! Schema::hasColumn('royalty_report_items', 'held_reason')) {
                $table->string('held_reason')->nullable()->after('payout_status');
            }
        });

        if (! Schema::hasTable('royalty_held_releases')) {
            Schema::create('royalty_held_releases', function (Blueprint $table) {
                $table->id();
                $table->string('employee_code')->index();
                $table->foreign('employee_code')->references('employee_code')->on('employees')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('merit_points')->nullable();
                $table->date('release_date');
                $table->text('notes')->nullable();
                $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalty_held_releases');

        Schema::table('royalty_report_items', function (Blueprint $table) {
            $table->dropColumn(['payout_status', 'held_reason']);
        });
    }
};
