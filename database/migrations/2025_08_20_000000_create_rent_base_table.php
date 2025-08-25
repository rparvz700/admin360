<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rent_base', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->decimal('base_rent', 14, 2);
            $table->decimal('vat', 8, 2)->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->boolean('is_at_source')->default(false);
            $table->string('rent_type', 50)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rent_base');
    }
};
