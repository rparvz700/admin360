<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('asset_attributes')->onDelete('cascade');
            $table->text('value');   // flexible text storage for number/string/etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_attribute_values');
    }
};
