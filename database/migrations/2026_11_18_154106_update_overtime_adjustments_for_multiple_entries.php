<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_adjustments', function (Blueprint $table) {
            $table->index(['overtime_report_id', 'employee_id'], 'overtime_adjustments_report_employee_idx');
            $table->dropUnique('overtime_adjustments_overtime_report_id_employee_id_unique');

            $table->string('ot_type', 20)->default('normal')->after('employee_id')->index();
            $table->date('adjustment_date')->nullable()->after('ot_type');
            $table->foreignId('ot_block_id')->nullable()->after('adjustment_date')->constrained('ot_blocks')->nullOnDelete();
            $table->decimal('hours', 6, 2)->default(0)->after('ot_block_id');
            $table->foreignId('created_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
        });

        // Convert any legacy dual-column rows into individual single-type entries
        $legacyRows = DB::table('overtime_adjustments')->get();
        foreach ($legacyRows as $row) {
            $normal = (float) ($row->normal_adjustment ?? 0);
            $special = (float) ($row->special_adjustment ?? 0);

            if ($normal == 0.0 && $special == 0.0) {
                DB::table('overtime_adjustments')->where('id', $row->id)->delete();

                continue;
            }

            if ($normal != 0.0) {
                DB::table('overtime_adjustments')->where('id', $row->id)->update([
                    'ot_type' => 'normal',
                    'hours' => $normal,
                ]);
                if ($special != 0.0) {
                    DB::table('overtime_adjustments')->insert([
                        'overtime_report_id' => $row->overtime_report_id,
                        'employee_id' => $row->employee_id,
                        'ot_type' => 'special',
                        'hours' => $special,
                        'remarks' => $row->remarks,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            } else {
                DB::table('overtime_adjustments')->where('id', $row->id)->update([
                    'ot_type' => 'special',
                    'hours' => $special,
                ]);
            }
        }

        Schema::table('overtime_adjustments', function (Blueprint $table) {
            $table->dropColumn(['normal_adjustment', 'special_adjustment']);
        });
    }

    public function down(): void
    {
        Schema::table('overtime_adjustments', function (Blueprint $table) {
            $table->decimal('normal_adjustment', 6, 2)->default(0)->after('employee_id');
            $table->decimal('special_adjustment', 6, 2)->default(0)->after('normal_adjustment');
        });

        Schema::table('overtime_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('ot_block_id');
            $table->dropColumn(['ot_type', 'adjustment_date', 'hours']);
        });
    }
};
