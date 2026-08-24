<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->decimal('unit_charge_offpeak', 10, 2)->nullable()->after('sanctioned_load_kw');
            $table->decimal('unit_charge_peak', 10, 2)->nullable()->after('unit_charge_offpeak');
        });
    }

    public function down()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->dropColumn(['unit_charge_offpeak', 'unit_charge_peak']);
        });
    }
};
