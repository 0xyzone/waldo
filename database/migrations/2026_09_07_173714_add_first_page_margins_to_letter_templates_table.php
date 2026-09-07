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
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->boolean('different_first_page_margins')->default(false)->after('margin_right');
            $table->integer('first_page_margin_top')->nullable()->after('different_first_page_margins');
            $table->integer('first_page_margin_bottom')->nullable()->after('first_page_margin_top');
            $table->integer('first_page_margin_left')->nullable()->after('first_page_margin_bottom');
            $table->integer('first_page_margin_right')->nullable()->after('first_page_margin_left');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropColumn([
                'different_first_page_margins',
                'first_page_margin_top',
                'first_page_margin_bottom',
                'first_page_margin_left',
                'first_page_margin_right',
            ]);
        });
    }
};
