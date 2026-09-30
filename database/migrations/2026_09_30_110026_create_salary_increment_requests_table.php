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
        Schema::create('salary_increment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->nullable()->unique();
            $table->date('date_requested');
            $table->date('date_approved')->nullable();
            $table->date('date_applicable')->nullable();

            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->string('hod_id', 100)->nullable();
            $table->foreign('hod_id')->references('employee_code')->on('employees')->nullOnDelete();

            $table->string('employee_id', 100);
            $table->foreign('employee_id')->references('employee_code')->on('employees')->cascadeOnDelete();

            $table->foreignId('current_designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('proposed_designation_id')->nullable()->constrained('designations')->nullOnDelete();

            $table->decimal('current_salary', 12, 2)->nullable();
            $table->decimal('proposed_salary', 12, 2)->nullable();
            $table->decimal('increment_amount', 12, 2)->nullable();
            $table->decimal('increment_percentage', 6, 2)->nullable();

            $table->string('reason', 150)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('pending')->index();

            $table->boolean('hr_acknowledged')->default(false)->index();
            $table->foreignId('hr_acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_acknowledged_at')->nullable();
            $table->text('hr_notes')->nullable();

            $table->boolean('finance_acknowledged')->default(false)->index();
            $table->foreignId('finance_acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_acknowledged_at')->nullable();
            $table->text('finance_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_increment_requests');
    }
};
