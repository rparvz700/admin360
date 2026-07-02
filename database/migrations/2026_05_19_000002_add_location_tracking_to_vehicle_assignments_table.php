<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_assignments', 'location_tracking')) {
            Schema::table('vehicle_assignments', function (Blueprint $table) {
                $table->json('location_tracking')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_assignments', 'location_tracking')) {
            Schema::table('vehicle_assignments', function (Blueprint $table) {
                $table->dropColumn('location_tracking');
            });
        }
    }
};
