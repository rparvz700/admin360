<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('security_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->decimal('security_deposit_total', 14, 2)->nullable();
            $table->decimal('security_deposit_absorbable', 14, 2)->nullable();
            $table->decimal('security_deposit_non_absorbable', 14, 2)->nullable();
            $table->date('absorb_start_date')->nullable();
            $table->date('absorb_end_date')->nullable();
            $table->decimal('absorb_amount', 14, 2)->nullable();
            $table->decimal('absorb_amount_percentage', 5, 2)->nullable();
            $table->string('absorb_frequency', 50)->nullable();
            $table->string('method_description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('security_deposits');
    }
};
