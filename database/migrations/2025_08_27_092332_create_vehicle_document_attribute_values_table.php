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
        Schema::create('vehicle_document_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('vehicle_documents')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('vehicle_document_attributes')->onDelete('cascade');
            $table->text('value');   // stores string/number/date/etc. as text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_document_attribute_values');
    }
};
