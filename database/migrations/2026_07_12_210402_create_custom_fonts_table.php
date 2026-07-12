<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');       // Font family name, e.g. "MyCompanyFont"
            $table->string('file_name');          // Stored filename in storage/app/public/fonts
            $table->string('original_name');      // Original uploaded filename
            $table->string('style')->default('normal'); // normal | italic
            $table->string('weight')->default('400');    // 100 – 900
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fonts');
    }
};
