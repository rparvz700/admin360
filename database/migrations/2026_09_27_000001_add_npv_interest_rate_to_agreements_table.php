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
        if (Schema::hasTable('agreements') && !Schema::hasColumn('agreements', 'npv_interest_rate')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->decimal('npv_interest_rate', 5, 2)->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'npv_interest_rate')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropColumn('npv_interest_rate');
            });
        }
    }
};
