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
        Schema::table('leavers', function (Blueprint $table) {
            $table->boolean('publish_cl')->default(true)->after('hold_tips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leavers', function (Blueprint $table) {
            $table->dropColumn('publish_cl');
        });
    }
};
