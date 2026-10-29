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
        Schema::create('royalty_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('month'); // e.g. January
            $table->unsignedSmallInteger('year'); // e.g. 2026
            $table->date('cutoff_date');
            $table->string('attendance_file_path')->nullable();
            $table->string('performance_file_path')->nullable();
            $table->string('status')->default('generated'); // 'generated', 'validated'
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalty_reports');
    }
};
