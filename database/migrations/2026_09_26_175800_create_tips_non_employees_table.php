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
        Schema::create('tips_non_employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->index();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->decimal('tips_percentage', 5, 2)->default(100.00);
            $table->decimal('distribution_amount', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips_non_employees');
    }
};
