<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rent_increments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->foreignId('base_rent_id')->constrained('rent_base')->onDelete('cascade');
            $table->decimal('incremented_amount', 14, 2)->nullable();
            $table->date('increment_start_date')->nullable();
            $table->date('increment_end_date')->nullable();
            $table->decimal('increment_amount', 14, 2)->nullable();
            $table->decimal('increment_percentage', 5, 2)->nullable();
            $table->string('increment_frequency', 50)->nullable();
            $table->string('method_description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rent_increments');
    }
};
