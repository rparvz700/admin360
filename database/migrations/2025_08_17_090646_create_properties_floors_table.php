<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('properties_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('properties_building')->onDelete('cascade');
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->onDelete('set null');
            $table->foreignId('owner_id')->nullable()->constrained('owners')->onDelete('set null');
            $table->string('floor_label')->nullable();
            $table->decimal('floor_area_sft', 12, 2)->nullable();
            $table->string('premises_type')->nullable();
            $table->integer('car_parking')->nullable();
            $table->decimal('dg_space_sft', 12, 2)->nullable();
            $table->decimal('store_space_sft', 12, 2)->nullable();
            $table->string('project_name')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('properties_floors');
    }
};
