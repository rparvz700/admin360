<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agreement_utilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->foreignId('utility_type_id')->constrained('utility_types')->onDelete('cascade');
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->boolean('disburse_with_rent')->default(true);
            $table->timestamps();

            $table->unique(['agreement_id', 'utility_type_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('agreement_utilities');
    }
};
