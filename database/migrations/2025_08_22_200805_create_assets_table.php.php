<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();   // e.g., INV-001
            $table->string('asset_name');            // e.g., Air Conditioner
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('cascade');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();

            // 🔗 Linking to floor (instead of separate locations table)
            $table->foreignId('floor_id')->nullable()->constrained('properties_floors')->onDelete('set null');
            $table->string('location_within_floor'); 
            
            // 🔗 Self-referencing parent asset (for outdoor AC → indoor ACs)
            $table->foreignId('parent_id')->nullable()->constrained('assets')->onDelete('cascade');
            $table->enum('status', ['active', 'repair', 'retired'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
