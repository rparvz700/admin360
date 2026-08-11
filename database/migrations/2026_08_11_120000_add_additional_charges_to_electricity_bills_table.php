<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $table->decimal('late_fee', 12, 2)->default(0)->after('vat_amount');
            $table->decimal('meter_charge', 12, 2)->default(0)->after('late_fee');
            $table->decimal('others_amount', 12, 2)->default(0)->after('meter_charge');
        });
    }

    public function down()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $table->dropColumn(['late_fee', 'meter_charge', 'others_amount']);
        });
    }
};
