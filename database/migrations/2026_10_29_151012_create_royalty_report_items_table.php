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
        Schema::create('royalty_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('royalty_report_id')->constrained('royalty_reports')->cascadeOnDelete();
            $table->string('employee_code')->index();
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('designation')->nullable();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->string('employee_status')->default('Active');
            $table->decimal('annual_budget', 12, 2)->default(0);
            $table->decimal('monthly_max_payout', 12, 2)->default(0);
            $table->decimal('monthly_min_payout', 12, 2)->default(0);
            $table->boolean('is_exempt')->default(false); // If true, gets max payout regardless of deductions
            $table->decimal('absent_days', 8, 2)->default(0);
            $table->unsignedInteger('late_early_count')->default(0);
            $table->unsignedInteger('missing_thumb_count')->default(0);
            $table->decimal('performance_deduction_percent', 5, 2)->default(0);
            $table->decimal('performance_deduction_amount', 12, 2)->default(0);
            $table->decimal('attendance_deduction_amount', 12, 2)->default(0);
            $table->decimal('total_deduction_amount', 12, 2)->default(0);
            $table->decimal('final_payout', 12, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalty_report_items');
    }
};
