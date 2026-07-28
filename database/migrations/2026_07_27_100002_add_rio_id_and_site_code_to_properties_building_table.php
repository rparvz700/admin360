<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties_building', function (Blueprint $table) {
            $table->foreignId('rio_id')->nullable()->constrained('rios')->onDelete('set null');
            $table->string('site_code', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::table('properties_building', function (Blueprint $table) {
            $table->dropForeign(['rio_id']);
            $table->dropColumn(['rio_id', 'site_code']);
        });
    }
};
