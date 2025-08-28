<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Foreign key
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->onDelete('cascade');

            // Vehicle details
            $table->string('registration_number')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->year('manufacture_year')->nullable();
            $table->string('color')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->string('engine_number')->nullable()->unique();
            $table->string('chassis_number')->nullable()->unique();

            // Usage info
            $table->string('use_purpose')->nullable();
            $table->string('use_company')->nullable();
            $table->boolean('isRented')->default(false);

            // Purchase info
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();

            // Lifecycle status
            $table->string('status')->default('active'); // active, inactive, scrapped

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
