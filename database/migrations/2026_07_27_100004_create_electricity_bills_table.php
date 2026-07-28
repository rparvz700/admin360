<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('electricity_bills', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no', 100)->unique();
            $table->enum('bill_type', ['postpaid', 'prepaid']);
            $table->foreignId('meter_id')->constrained('electricity_meters')->onDelete('cascade');
            $table->foreignId('building_id')->constrained('properties_building')->onDelete('cascade');
            $table->foreignId('rio_id')->nullable()->constrained('rios')->onDelete('set null');
            $table->string('project_name', 100)->default('BR Project');
            $table->string('billing_month', 20)->nullable(); // e.g. Sep'26
            
            // Meter reading values (for postpaid)
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('units_consumed', 12, 2)->default(0);
            $table->decimal('rate_per_unit', 10, 2)->default(0);
            
            // Financial amounts
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            
            // Dates & Payment Requisition Details
            $table->date('received_subcenter_date')->nullable();
            $table->date('last_payment_date')->nullable();
            $table->string('cheque_name', 255)->default('Govt. Revenue Collection');
            $table->enum('payment_mode', ['BEFTN', 'Cheque', 'bKash', 'Cash'])->default('BEFTN');
            $table->text('payment_account_details')->nullable();
            
            // Workflow & Status
            $table->enum('status', ['generated', 'paid', 'cancelled'])->default('generated');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Accounts Payment Execution
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference', 100)->nullable(); // Transaction ID / Cheque No / BEFTN Ref
            
            $table->string('bill_file_path', 255)->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('electricity_bills');
    }
};
