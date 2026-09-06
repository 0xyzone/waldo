<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing date values to formatted string before changing column type
        DB::table('biometric_allotments')
            ->whereNotNull('join_date')
            ->get(['code', 'join_date'])
            ->each(function ($row) {
                $formatted = Carbon::parse($row->join_date)->format('d F, Y');
                DB::table('biometric_allotments')
                    ->where('code', $row->code)
                    ->update(['join_date' => $formatted]);
            });

        Schema::table('biometric_allotments', function (Blueprint $table) {
            $table->string('join_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert formatted strings back to Y-m-d date format
        DB::table('biometric_allotments')
            ->whereNotNull('join_date')
            ->get(['code', 'join_date'])
            ->each(function ($row) {
                try {
                    $date = Carbon::createFromFormat('d F, Y', $row->join_date)?->format('Y-m-d');
                } catch (Throwable) {
                    $date = null;
                }
                DB::table('biometric_allotments')
                    ->where('code', $row->code)
                    ->update(['join_date' => $date]);
            });

        Schema::table('biometric_allotments', function (Blueprint $table) {
            $table->date('join_date')->nullable()->change();
        });
    }
};
