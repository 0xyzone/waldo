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
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');

            // Define the constraint manually right below it
            $table->foreign('employee_id')->references('employee_code')->on('employees')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('for_month')->nullable();
            $table->text('notes_by_hr')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes_by_finance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
