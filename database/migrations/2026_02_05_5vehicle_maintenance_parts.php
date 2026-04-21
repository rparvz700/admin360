<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_maintenance_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id')->constrained('vehicle_maintenances')->onDelete('cascade');
            $table->foreignId('vehicle_part_id')->constrained('vehicle_parts')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            
            $table->enum('action_type', ['replace', 'repair', 'service'])->default('replace');
            $table->integer('quantity')->default(1);
            
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('part_cost', 12, 2);
            
            // Warranty Tracking
            $table->integer('warranty_period_months')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            
            // Next Replacement Tracking
            $table->date('next_replacement_due_date')->nullable();
            $table->integer('next_replacement_due_km')->nullable();
            
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'vehicle_part_id']);
            $table->index(['next_replacement_due_date', 'next_replacement_due_km']);
            $table->index('warranty_expiry_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance_parts');
    }
};