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
        Schema::create('schedule_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('status')->default('running'); // running, success, failed
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->decimal('duration_seconds', 8, 2)->nullable();
            $table->integer('exit_code')->nullable();
            $table->longText('output')->nullable();
            $table->timestamps();

            $table->index(['command', 'started_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_runs');
    }
};
