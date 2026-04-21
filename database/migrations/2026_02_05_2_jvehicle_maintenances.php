<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->enum('maintenance_type', ['routine', 'breakdown', 'accident', 'inspection'])->default('routine');
            $table->dateTime('start_datetime');
            $table->dateTime('estimated_end_datetime')->nullable();
            $table->dateTime('actual_end_datetime')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('service_description');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->integer('meter_reading_at_service');
            $table->decimal('total_service_cost', 12, 2);
            
            // Service Due Tracking
            $table->date('next_service_due_date')->nullable();
            $table->integer('next_service_due_km')->nullable();
            $table->boolean('current_service_completed')->default(true);
            
            // Cost Breakdown
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('parts_cost', 12, 2)->default(0);
            
            // Additional Info
            $table->json('parts_replaced')->nullable();
            $table->string('performed_by')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['vehicle_id', 'start_datetime']);
            $table->index('maintenance_type');
            $table->index(['next_service_due_date', 'next_service_due_km']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};