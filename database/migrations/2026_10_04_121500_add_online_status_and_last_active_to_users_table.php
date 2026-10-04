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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'online_status_enabled')) {
                    $table->boolean('online_status_enabled')->default(true)->after('read_receipts_enabled');
                }
                if (! Schema::hasColumn('users', 'last_active_at')) {
                    $table->timestamp('last_active_at')->nullable()->after('online_status_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'last_active_at')) {
                    $table->dropColumn('last_active_at');
                }
                if (Schema::hasColumn('users', 'online_status_enabled')) {
                    $table->dropColumn('online_status_enabled');
                }
            });
        }
    }
};
