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
        Schema::create('tips_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->date('cutoff_date');
            $table->string('excel_file_path')->nullable();
            $table->json('collection_summary')->nullable();
            $table->string('status')->default('draft'); // draft, generated, locked
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tips_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tips_report_id')->constrained('tips_reports')->cascadeOnDelete();
            $table->string('employee_id')->nullable();
            $table->string('employee_code')->nullable()->index();
            $table->string('department')->nullable()->index();
            $table->string('designation')->nullable();
            $table->string('employee_name')->nullable();

            // Attendance metrics
            $table->decimal('working_days', 8, 2)->default(0);
            $table->decimal('present_days', 8, 2)->default(0);
            $table->decimal('absent_days', 8, 2)->default(0);
            $table->decimal('total_leaves', 8, 2)->default(0);
            $table->integer('late_in_count')->default(0);
            $table->integer('early_out_count')->default(0);

            // Tenure metrics
            $table->string('join_date')->nullable();
            $table->string('working_duration')->nullable();
            $table->decimal('completion_factor', 5, 2)->default(1.0);

            // Tip configuration flags
            $table->decimal('point_value', 8, 4)->default(0);
            $table->decimal('base_tips_amount', 12, 2)->default(0);
            $table->decimal('tips_percentage', 8, 2)->default(100);
            $table->boolean('is_blank')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->boolean('publish_tips')->default(true);
            $table->string('tips_status')->default('Release');

            // Adjustments
            $table->decimal('amount_to_adjust', 12, 2)->default(0);
            $table->decimal('amount_to_deduct', 12, 2)->default(0);
            $table->decimal('percentage_to_deduct', 8, 2)->default(0);

            // Output amounts
            $table->decimal('calculated_tips', 12, 2)->default(0);
            $table->decimal('final_distribution_amount', 12, 2)->default(0);
            $table->boolean('is_left_out')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips_report_items');
        Schema::dropIfExists('tips_reports');
    }
};
