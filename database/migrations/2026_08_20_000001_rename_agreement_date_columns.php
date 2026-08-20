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
        if (Schema::hasTable('agreements')) {
            Schema::table('agreements', function (Blueprint $table) {
                if (Schema::hasColumn('agreements', 'from_date') && !Schema::hasColumn('agreements', 'payment_start_date')) {
                    $table->renameColumn('from_date', 'payment_start_date');
                }
                if (Schema::hasColumn('agreements', 'to_date') && !Schema::hasColumn('agreements', 'expiry_date')) {
                    $table->renameColumn('to_date', 'expiry_date');
                }
            });
        }

        if (Schema::hasTable('npv_agreement_summaries')) {
            Schema::table('npv_agreement_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('npv_agreement_summaries', 'from_date') && !Schema::hasColumn('npv_agreement_summaries', 'payment_start_date')) {
                    $table->renameColumn('from_date', 'payment_start_date');
                }
                if (Schema::hasColumn('npv_agreement_summaries', 'to_date') && !Schema::hasColumn('npv_agreement_summaries', 'expiry_date')) {
                    $table->renameColumn('to_date', 'expiry_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('agreements')) {
            Schema::table('agreements', function (Blueprint $table) {
                if (Schema::hasColumn('agreements', 'payment_start_date')) {
                    $table->renameColumn('payment_start_date', 'from_date');
                }
                if (Schema::hasColumn('agreements', 'expiry_date')) {
                    $table->renameColumn('expiry_date', 'to_date');
                }
            });
        }

        if (Schema::hasTable('npv_agreement_summaries')) {
            Schema::table('npv_agreement_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('npv_agreement_summaries', 'payment_start_date')) {
                    $table->renameColumn('payment_start_date', 'from_date');
                }
                if (Schema::hasColumn('npv_agreement_summaries', 'expiry_date')) {
                    $table->renameColumn('expiry_date', 'to_date');
                }
            });
        }
    }
};
