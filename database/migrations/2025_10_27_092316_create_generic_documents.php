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
        Schema::create('generic_documents', function (Blueprint $table) 
        {
            $table->id();
            $table->morphs('documentable'); // creates documentable_id (bigint) & documentable_type (string)
            $table->foreignId('category_id')
                ->constrained('generic_document_categories')
                ->onDelete('cascade');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable(); // scanned document
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_documents');
    }
};
