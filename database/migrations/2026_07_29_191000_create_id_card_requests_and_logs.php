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
        if (Schema::hasTable('employee_print_records')) {
            Schema::dropIfExists('employee_print_records');
        }

        Schema::create('id_card_requests', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('custom');
            $table->string('employee_code')->nullable();
            $table->string('employee_name');
            $table->string('employee_designation')->nullable();
            $table->string('employee_department')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('id_card_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_card_request_id')->constrained('id_card_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('action_description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_card_request_logs');
        Schema::dropIfExists('id_card_requests');
    }
};
