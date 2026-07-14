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
        Schema::create('biometric_allotments', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->nullable();
            $table->date('enrolled_date')->nullable();
            $table->string('set_by')->nullable();
            $table->boolean('old_checkout_device')->default(false);
            $table->boolean('new_checkin')->default(false);
            $table->boolean('new_checkout')->default(false);
            $table->string('shift')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_allotments');
    }
};
