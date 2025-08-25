<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->string('payment_type')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->decimal('gross', 14, 2)->nullable();
            $table->decimal('vat', 14, 2)->nullable();
            $table->decimal('tax', 14, 2)->nullable();
            $table->decimal('net_rent', 14, 2)->nullable();
            $table->decimal('less_advance', 14, 2)->nullable();
            $table->decimal('dg_space_rent', 14, 2)->nullable();
            $table->decimal('store_space_rent', 14, 2)->nullable();
            $table->decimal('service_charge', 14, 2)->nullable();
            $table->decimal('utilities', 14, 2)->nullable();
            $table->decimal('others', 14, 2)->nullable();
            $table->decimal('net_payable', 14, 2)->nullable();
            $table->string('payment_sheet_ref')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
