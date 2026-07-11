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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('employee_code')->primary();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('dp_rank')->nullable();
            $table->bigInteger('rank')->nullable();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->string('join_date_formatted')->nullable();
            $table->date('join_date')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('citizenship_number')->nullable();
            $table->string('citizenship_issue_date')->nullable();
            $table->string('citizenship_issue_place')->nullable();
            $table->string('ssid')->nullable();
            $table->date('dob_ad')->nullable();
            $table->string('dob_bs')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('employee_status')->nullable();
            $table->decimal('tips_amount', 15, 2)->nullable();
            $table->string('tips_status')->nullable();
            $table->decimal('point_value', 10, 4)->nullable();
            $table->boolean('tips_blank')->nullable();
            $table->boolean('publish_tips')->nullable();
            $table->boolean('tips_fixed')->nullable();
            $table->string('hrms_password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
