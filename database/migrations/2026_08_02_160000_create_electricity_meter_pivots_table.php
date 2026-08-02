<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('electricity_meter_floors')) {
            Schema::create('electricity_meter_floors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meter_id')->constrained('electricity_meters')->onDelete('cascade');
                $table->foreignId('floor_id')->constrained('properties_floors')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['meter_id', 'floor_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('electricity_meter_floors');
    }
};
