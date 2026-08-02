<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->string('authority_name', 100)->nullable()->after('provider_name');
            $table->string('payment_process', 50)->nullable()->after('authority_name');
            $table->string('meter_owner', 50)->nullable()->after('payment_process');
        });
    }

    public function down()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->dropColumn(['authority_name', 'payment_process', 'meter_owner']);
        });
    }
};
