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
        Schema::create('unofficial_nametag_requests', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('custom'); // 'employee' or 'custom'
            $table->string('employee_code')->nullable()->index();
            $table->string('employee_name');
            $table->string('name_on_tag')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending')->index(); // pending, in_progress, ready, distributed, cancelled
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('unofficial_nametag_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unofficial_nametag_request_id')
                ->constrained('unofficial_nametag_requests', 'id', 'fk_untr_logs_req_id')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('unofficial_nametag_request_logs');
        Schema::dropIfExists('unofficial_nametag_requests');
    }
};
