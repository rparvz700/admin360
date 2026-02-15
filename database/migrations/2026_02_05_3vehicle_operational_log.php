<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_operational_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->enum('log_type', ['assignment', 'meter_reading', 'status_change']);
            $table->string('assigned_department')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('meter_reading')->nullable();
            $table->enum('vehicle_status', ['active', 'inactive', 'sold', 'under_maintenance'])->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('logged_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('logged_at');
            $table->timestamps();
            
            $table->index(['vehicle_id', 'log_type']);
            $table->index('logged_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_operational_logs');
    }
};