<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Name column was already added to the original create migration.
     * This migration is a no-op kept for production rollback safety.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('discord_settings', 'name')) {
            Schema::table('discord_settings', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Column is included in the base create migration; nothing to drop here.
    }
};
