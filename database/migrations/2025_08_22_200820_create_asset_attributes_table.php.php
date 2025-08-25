<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('cascade');
            $table->string('attribute_name');     // e.g., Power Rating
            $table->enum('attribute_type', ['string', 'number', 'date', 'boolean', 'select']);
            $table->text('options')->nullable()->after('attribute_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_attributes');
    }
};
