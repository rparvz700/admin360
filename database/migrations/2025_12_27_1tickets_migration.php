<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->enum('ticket_type', ['vehicle_support', 'asset_request', 'asset_repair']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // Vehicle Support specific fields
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->onDelete('set null');
            $table->dateTime('trip_start_datetime')->nullable();
            $table->dateTime('trip_end_datetime')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('destination')->nullable();
            $table->integer('passenger_count')->nullable();
            $table->text('trip_purpose')->nullable();
            
            // Asset related fields
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('set null');
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->onDelete('set null');
            $table->string('requested_asset_name')->nullable();
            $table->text('asset_specifications')->nullable();
            $table->foreignId('floor_id')->nullable()->constrained('properties_floors')->onDelete('set null');
            $table->string('location_within_floor')->nullable();
            
            // Assignment fields
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
