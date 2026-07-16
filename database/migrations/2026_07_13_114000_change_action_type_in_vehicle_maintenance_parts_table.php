<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, drop the ENUM check constraint so we can store any action string
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE vehicle_maintenance_parts DROP CONSTRAINT IF EXISTS vehicle_maintenance_parts_action_type_check");
            DB::statement("ALTER TABLE vehicle_maintenance_parts ALTER COLUMN action_type TYPE VARCHAR(255)");
        } else {
            Schema::table('vehicle_maintenance_parts', function (Blueprint $table) {
                $table->string('action_type')->default('replace_brand_new')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_parts', function (Blueprint $table) {
            $table->enum('action_type', ['replace', 'repair', 'service'])->default('replace')->change();
        });
    }
};
