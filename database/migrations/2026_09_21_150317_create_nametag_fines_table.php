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
        Schema::create('nametag_fines', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason'); // Lost, Damaged, etc.
            $table->decimal('amount', 8, 2)->default(500);
            $table->string('for_month'); // e.g. "september"
            $table->year('for_year'); // e.g. 2026
            $table->boolean('acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_code')->on('employees')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nametag_fines');
    }
};
