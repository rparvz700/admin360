<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'billing_month')) {
                $table->char('billing_month', 7)->nullable()->after('remarks');
                $table->index('billing_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'billing_month')) {
                $table->dropIndex(['billing_month']);
                $table->dropColumn('billing_month');
            }
        });
    }
};
