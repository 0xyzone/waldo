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
        Schema::create('monthly_manual_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->string('title')->nullable();
            $table->unsignedInteger('total_employees')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->string('status', 50)->default('in_progress'); // in_progress, completed, empty
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', indexName: 'fk_mma_created_by')->nullOnDelete();
            $table->timestamps();

            $table->unique(['year', 'month']);
        });

        Schema::create('monthly_manual_attendance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_manual_attendance_id')->constrained('monthly_manual_attendances', indexName: 'fk_mma_items_att_id')->cascadeOnDelete();
            $table->string('employee_code');
            $table->string('employee_name');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->boolean('is_attendance_updated')->default(false)->index();
            $table->timestamp('updated_at_hrms')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', indexName: 'fk_mma_items_updated_by')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_code', 'fk_mma_items_emp_code')->references('employee_code')->on('employees')->cascadeOnDelete();
            $table->unique(['monthly_manual_attendance_id', 'employee_code'], 'idx_mma_emp_unique');
            $table->index(['monthly_manual_attendance_id', 'is_attendance_updated'], 'idx_mma_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_manual_attendance_items');
        Schema::dropIfExists('monthly_manual_attendances');
    }
};
