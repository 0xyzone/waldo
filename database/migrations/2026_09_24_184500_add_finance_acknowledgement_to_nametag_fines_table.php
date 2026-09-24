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
        Schema::table('nametag_fines', function (Blueprint $table) {
            $table->boolean('finance_acknowledged')->default(false)->after('acknowledged_at');
            $table->foreignId('finance_acknowledged_by')->nullable()->after('finance_acknowledged')->constrained('users')->nullOnDelete();
            $table->timestamp('finance_acknowledged_at')->nullable()->after('finance_acknowledged_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nametag_fines', function (Blueprint $table) {
            $table->dropForeign(['finance_acknowledged_by']);
            $table->dropColumn(['finance_acknowledged', 'finance_acknowledged_by', 'finance_acknowledged_at']);
        });
    }
};
