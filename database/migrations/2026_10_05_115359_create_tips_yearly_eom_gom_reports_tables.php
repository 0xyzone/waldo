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
        Schema::create('tips_eom_gom_excluded_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->unique()->constrained('departments')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tips_yearly_eom_gom_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->string('title')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tips_yearly_eom_gom_report_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id', 'fk_tye_report_id')
                ->references('id')
                ->on('tips_yearly_eom_gom_reports')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('month_number'); // 1 - 12
            $table->string('month_name'); // January - December
            $table->unsignedTinyInteger('entry_number'); // 1, 2, 3
            $table->boolean('is_gaming_slot')->default(false);

            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id', 'fk_tye_dept_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->string('department_name')->nullable();

            $table->string('eom_employee_code_1')->nullable();
            $table->foreign('eom_employee_code_1', 'fk_tye_eom1')
                ->references('employee_code')
                ->on('employees')
                ->nullOnDelete();

            $table->string('eom_employee_code_2')->nullable();
            $table->foreign('eom_employee_code_2', 'fk_tye_eom2')
                ->references('employee_code')
                ->on('employees')
                ->nullOnDelete();

            $table->string('gom_employee_code_1')->nullable();
            $table->foreign('gom_employee_code_1', 'fk_tye_gom1')
                ->references('employee_code')
                ->on('employees')
                ->nullOnDelete();

            $table->text('eom_remarks_1')->nullable();
            $table->text('eom_remarks_2')->nullable();
            $table->text('gom_remarks_1')->nullable();

            $table->timestamps();

            $table->index(['report_id', 'month_number'], 'idx_tye_report_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips_yearly_eom_gom_report_entries');
        Schema::dropIfExists('tips_yearly_eom_gom_reports');
        Schema::dropIfExists('tips_eom_gom_excluded_departments');
    }
};
