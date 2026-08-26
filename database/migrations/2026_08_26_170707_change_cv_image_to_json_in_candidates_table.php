<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Wrap any existing single-string values into a JSON array before changing column type
        DB::table('candidates')
            ->whereNotNull('cv_image')
            ->where('cv_image', 'not like', '[%')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('candidates')
                    ->where('id', $row->id)
                    ->update(['cv_image' => json_encode([$row->cv_image])]);
            });

        Schema::table('candidates', function (Blueprint $table) {
            $table->json('cv_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('cv_image')->nullable()->change();
        });
    }
};
