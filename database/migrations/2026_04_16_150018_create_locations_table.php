<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('upazilla')->nullable();
            $table->string('district')->nullable();
            $table->string('district_code')->nullable();
            $table->string('district_kmz_code')->nullable();
            $table->string('division')->nullable();
            $table->string('subcenter')->nullable();
            $table->string('rio')->nullable();
            $table->string('thana_short_code')->nullable();
            $table->integer('district_opus_id')->nullable();
            $table->tinyInteger('is_metro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
