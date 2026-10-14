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
        Schema::table('terminated_employees', function (Blueprint $table) {
            if (! Schema::hasColumn('terminated_employees', 'is_letter_printed')) {
                $table->boolean('is_letter_printed')->default(false)->after('reason');
            }
            if (! Schema::hasColumn('terminated_employees', 'letter_printed_at')) {
                $table->timestamp('letter_printed_at')->nullable()->after('is_letter_printed');
            }
            if (! Schema::hasColumn('terminated_employees', 'is_letter_released')) {
                $table->boolean('is_letter_released')->default(false)->after('letter_printed_at');
            }
            if (! Schema::hasColumn('terminated_employees', 'letter_released_at')) {
                $table->timestamp('letter_released_at')->nullable()->after('is_letter_released');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminated_employees', function (Blueprint $table) {
            $columns = ['letter_released_at', 'is_letter_released', 'letter_printed_at', 'is_letter_printed'];
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('terminated_employees', $col));
            if (! empty($existing)) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
