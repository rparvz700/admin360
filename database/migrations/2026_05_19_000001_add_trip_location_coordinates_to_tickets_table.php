<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'trip_location_coordinates')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('trip_location_coordinates')->nullable()->after('trip_location_details');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'trip_location_coordinates')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('trip_location_coordinates');
            });
        }
    }
};
