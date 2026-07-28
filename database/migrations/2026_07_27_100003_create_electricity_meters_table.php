<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('electricity_meters', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number', 100);
            $table->enum('meter_type', ['postpaid_main', 'postpaid_sub', 'prepaid']);
            $table->string('provider_name', 100)->nullable(); // DESCO, DPDC, NESCO, BREB, WZPDCL, House Owner
            $table->foreignId('building_id')->constrained('properties_building')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('properties_floors')->onDelete('set null');
            $table->foreignId('owner_id')->nullable()->constrained('owners')->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->string('consumer_no', 100)->nullable();
            $table->decimal('sanctioned_load_kw', 8, 2)->nullable();
            $table->string('meter_location_notes', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('electricity_meters');
    }
};
