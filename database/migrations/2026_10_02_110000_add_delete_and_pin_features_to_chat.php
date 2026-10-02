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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('body');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->boolean('is_pinned')->default(false)->after('file_size');
            $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            $table->timestamp('pinned_until')->nullable()->after('pinned_at');
            $table->foreignId('pinned_by')->nullable()->after('pinned_until')->constrained('users')->nullOnDelete();
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('last_read_at');
            $table->timestamp('pinned_until')->nullable()->after('is_pinned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'pinned_until']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['pinned_by']);
            $table->dropColumn(['is_deleted', 'deleted_at', 'is_pinned', 'pinned_at', 'pinned_until', 'pinned_by']);
        });
    }
};
