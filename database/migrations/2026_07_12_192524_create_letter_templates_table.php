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
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->json('variables')->nullable();
            $table->integer('margin_top')->default(25);
            $table->integer('margin_bottom')->default(25);
            $table->integer('margin_left')->default(20);
            $table->integer('margin_right')->default(20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};
