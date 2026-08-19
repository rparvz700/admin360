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
        if (!Schema::hasTable('npv_agreement_summaries')) {
            Schema::create('npv_agreement_summaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agreement_id');
                $table->decimal('discount_rate', 5, 2);
                $table->string('agreement_ref_no');
                $table->string('vendor_name')->nullable();
                $table->string('site_name')->nullable();
                $table->string('from_date', 50)->nullable();
                $table->string('to_date', 50)->nullable();
                $table->integer('total_months')->default(0);
                $table->decimal('total_npv', 18, 2)->default(0.00);
                $table->decimal('total_undiscounted_outflow', 18, 2)->default(0.00);
                $table->decimal('total_gross_rent', 18, 2)->default(0.00);
                $table->decimal('total_advance_deductions', 18, 2)->default(0.00);
                $table->decimal('total_deposit_refunds', 18, 2)->default(0.00);
                $table->timestamp('calculated_at')->nullable();
                $table->timestamps();

                $table->unique(['agreement_id', 'discount_rate'], 'npv_agr_rate_unique');
                $table->index(['discount_rate', 'total_npv'], 'npv_rate_val_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npv_agreement_summaries');
    }
};
