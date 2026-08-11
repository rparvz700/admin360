<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('electricity_bills', 'previous_peak_reading')) {
                $table->decimal('previous_peak_reading', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('electricity_bills', 'current_peak_reading')) {
                $table->decimal('current_peak_reading', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('electricity_bills', 'units_peak_consumed')) {
                $table->decimal('units_peak_consumed', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('electricity_bills', 'rate_peak_per_unit')) {
                $table->decimal('rate_peak_per_unit', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('electricity_bills', 'amount_peak')) {
                $table->decimal('amount_peak', 14, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('electricity_bills', 'amount_offpeak')) {
                $table->decimal('amount_offpeak', 14, 2)->default(0)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $columns = [
                'previous_peak_reading',
                'current_peak_reading',
                'units_peak_consumed',
                'rate_peak_per_unit',
                'amount_peak',
                'amount_offpeak',
            ];
            
            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('electricity_bills', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
