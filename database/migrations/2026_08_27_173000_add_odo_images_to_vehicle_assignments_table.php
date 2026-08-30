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
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->string('start_odo_image')->nullable()->after('start_odo_meter');
            $table->string('end_odo_image')->nullable()->after('end_odo_meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropColumn(['start_odo_image', 'end_odo_image']);
        });
    }
};
