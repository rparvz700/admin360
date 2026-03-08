<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_name');
            $table->string('part_code')->unique();
            $table->string('category');
            $table->text('description')->nullable();
            $table->integer('typical_lifespan_km')->nullable();
            $table->integer('typical_lifespan_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('part_code');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_parts');
    }
};