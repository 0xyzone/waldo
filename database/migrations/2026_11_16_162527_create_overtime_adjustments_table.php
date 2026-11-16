<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_report_id')->constrained('overtime_reports')->cascadeOnDelete();
            $table->string('employee_id')->index();
            $table->decimal('normal_adjustment', 6, 2)->default(0);
            $table->decimal('special_adjustment', 6, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['overtime_report_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_adjustments');
    }
};
