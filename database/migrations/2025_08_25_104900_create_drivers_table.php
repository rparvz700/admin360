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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id(); // will map to "id" from JSON

            // Basic Info
            $table->string('hr_id')->unique();
            $table->string('name');
            $table->string('sur_name')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('employment_contract')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('image_path')->nullable();

            // Employment details
            $table->boolean('contract_renewed')->default(0);
            $table->date('confirmation_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('division')->nullable();
            $table->string('office_location')->nullable();
            $table->string('subcenter')->nullable();
            $table->string('job_location')->nullable();

            // Supervisor info
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_email')->nullable();
            $table->string('supervisor_hr_id')->nullable();
            $table->string('supervisor_company')->nullable();

            // Bill reviewer info
            $table->string('bill_reviewer_name')->nullable();
            $table->string('bill_reviewer_email')->nullable();
            $table->string('bill_reviewer_hr_id')->nullable();
            $table->string('bill_reviewer_company')->nullable();

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
