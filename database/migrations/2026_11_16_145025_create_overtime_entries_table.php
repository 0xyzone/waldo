<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_entries', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->index();
            $table->date('ot_date')->index();
            $table->foreignId('ot_block_id')->constrained('ot_blocks')->cascadeOnDelete();
            $table->decimal('hours', 6, 2);
            $table->boolean('is_special_ot')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_entries');
    }
};
