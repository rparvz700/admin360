<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('properties_building', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('site_name')->nullable();
            $table->string('country')->nullable();
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('area')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 11, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('properties_building');
    }
};
