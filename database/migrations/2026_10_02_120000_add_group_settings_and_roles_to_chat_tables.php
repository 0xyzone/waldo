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
        Schema::table('conversations', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->boolean('only_admins_can_message')->default(false)->after('avatar_url');
            $table->boolean('only_admins_can_edit_info')->default(true)->after('only_admins_can_message');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->string('role')->default('member')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['description', 'only_admins_can_message', 'only_admins_can_edit_info']);
        });
    }
};
