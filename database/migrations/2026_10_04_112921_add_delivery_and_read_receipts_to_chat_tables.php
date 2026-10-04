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
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'read_receipts_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('read_receipts_enabled')->default(true)->after('must_change_password');
            });
        }

        if (Schema::hasTable('conversation_participants') && ! Schema::hasColumn('conversation_participants', 'last_delivered_at')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                $table->timestamp('last_delivered_at')->nullable()->after('last_read_at');
            });
        }

        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'delivered_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->timestamp('delivered_at')->nullable()->after('is_forwarded');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'delivered_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('delivered_at');
            });
        }

        if (Schema::hasTable('conversation_participants') && Schema::hasColumn('conversation_participants', 'last_delivered_at')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                $table->dropColumn('last_delivered_at');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'read_receipts_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('read_receipts_enabled');
            });
        }
    }
};
