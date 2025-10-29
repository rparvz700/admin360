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
        Schema::create('generic_document_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('generic_documents')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('generic_document_attributes')->onDelete('cascade');
            $table->text('value');   // stores string/number/date/etc. as text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_document_attribute_values');
    }
};
