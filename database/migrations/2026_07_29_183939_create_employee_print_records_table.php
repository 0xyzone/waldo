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
        Schema::create('employee_print_records', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('custom');
            $table->string('employee_code')->nullable();
            $table->string('employee_name');
            $table->string('employee_designation')->nullable();
            $table->string('employee_department')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_print_records');
    }
};
