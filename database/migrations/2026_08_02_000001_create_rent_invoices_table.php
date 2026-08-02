<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_base_id')->constrained('rent_base')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->char('billing_month', 7); // Format: "YYYY-MM" (e.g. "2026-08")
            $table->timestamps();

            $table->unique(['rent_base_id', 'billing_month']);
            $table->index('billing_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_invoices');
    }
};
