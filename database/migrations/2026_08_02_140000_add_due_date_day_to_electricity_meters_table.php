<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->unsignedTinyInteger('due_date_day')->nullable()->after('consumer_no');
        });
    }

    public function down()
    {
        Schema::table('electricity_meters', function (Blueprint $table) {
            $table->dropColumn('due_date_day');
        });
    }
};
