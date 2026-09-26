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
            if (! Schema::hasColumn('tips_reports', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('tips_reports', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips_reports', function (Blueprint $table) {
            if (Schema::hasColumn('tips_reports', 'validated_by')) {
                $table->dropForeign(['validated_by']);
                $table->dropColumn('validated_by');
            }
            if (Schema::hasColumn('tips_reports', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
        });
    }
};
