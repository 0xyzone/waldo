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
        Schema::table('tips_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('tips_reports', 'held_tips')) {
                $table->json('held_tips')->nullable()->after('left_outs');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_reports', function (Blueprint $table) {
            if (Schema::hasColumn('tips_reports', 'held_tips')) {
                $table->dropColumn('held_tips');
            }
        });
    }
};
