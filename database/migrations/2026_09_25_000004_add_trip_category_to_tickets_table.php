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
        if (!Schema::hasColumn('tickets', 'trip_category')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('trip_category')->nullable()->after('vehicle_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'trip_category')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('trip_category');
            });
        }
    }
};
