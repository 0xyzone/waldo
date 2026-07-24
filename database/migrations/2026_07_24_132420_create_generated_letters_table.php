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
        Schema::create('generated_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_template_id')->nullable()->constrained('letter_templates')->nullOnDelete();
            $table->string('employee_code')->nullable();
            $table->foreign('employee_code')->references('employee_code')->on('employees')->nullOnDelete();
            $table->string('template_title');
            $table->string('employee_name')->nullable();
            $table->longText('content');
            $table->json('custom_values')->nullable();
            $table->json('margins')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_letters');
    }
};
