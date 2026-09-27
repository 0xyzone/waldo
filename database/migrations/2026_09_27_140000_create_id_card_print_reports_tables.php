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
        Schema::dropIfExists('id_card_print_report_items');
        Schema::dropIfExists('id_card_print_reports');

        Schema::create('id_card_print_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('batch_date');
            $table->string('csv_file_path')->nullable();
            $table->unsignedInteger('total_records')->default(0);
            $table->string('status', 50)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('id_card_print_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_card_print_report_id')->constrained('id_card_print_reports')->cascadeOnDelete();
            $table->string('employee_code', 100)->index();
            $table->string('employee_name');
            $table->string('department')->nullable()->index();
            $table->string('designation')->nullable();
            $table->string('status', 50)->default('printed')->index();
            $table->string('csv_name')->nullable();
            $table->string('csv_department')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamps();

            $table->index(['id_card_print_report_id', 'status'], 'idx_icpri_report_status');
            $table->index(['id_card_print_report_id', 'department'], 'idx_icpri_report_dept');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_card_print_report_items');
        Schema::dropIfExists('id_card_print_reports');
    }
};
