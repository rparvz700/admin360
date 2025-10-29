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
        Schema::create('generic_document_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('generic_document_categories')->onDelete('cascade');
            $table->string('attribute_name');   
            $table->enum('attribute_type', ['string', 'number', 'date', 'boolean', 'select']);
            $table->text('options')->nullable();  // for select dropdown (comma-separated or JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_document_attributes');
    }
};
