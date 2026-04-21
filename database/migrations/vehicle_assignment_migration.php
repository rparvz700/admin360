<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vehicle Assignments - Track ongoing assignments
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for quick lookups
            $table->index(['vehicle_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['start_datetime', 'end_datetime']);
        });

        // Driver Availability - Track driver status
        Schema::create('driver_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->enum('status', ['available', 'on_assignment', 'on_leave', 'sick', 'unavailable'])->default('available');
            $table->dateTime('unavailable_from')->nullable();
            $table->dateTime('unavailable_until')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            
            // Only one active status per driver
            $table->unique('driver_id');
        });

        // Vehicle Maintenance - Track maintenance schedules
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->enum('maintenance_type', ['scheduled', 'repair', 'inspection', 'emergency'])->default('scheduled');
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->dateTime('start_datetime');
            $table->dateTime('estimated_end_datetime');
            $table->dateTime('actual_end_datetime')->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance');
        Schema::dropIfExists('driver_availability');
        Schema::dropIfExists('vehicle_assignments');
    }
};
