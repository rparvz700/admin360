<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Automatically drop postgres enum check constraints and change column to varchar
        try {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE vehicle_maintenance_parts DROP CONSTRAINT IF EXISTS vehicle_maintenance_parts_action_type_check");
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE vehicle_maintenance_parts ALTER COLUMN action_type TYPE VARCHAR(255)");
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('tickets') && !\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'trip_category')) {
                \Illuminate\Support\Facades\Schema::table('tickets', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('trip_category')->nullable()->after('vehicle_type_id');
                });
            }
        } catch (\Exception $e) {
            // Fail silently if DB is not ready/migrated yet
        }

        // Auto-sync Data Dictionary whenever migrations finish running
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\MigrationsEnded::class,
            function () {
                try {
                    app(\App\Services\DataDictionarySyncService::class)->sync();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('DataDictionary auto-sync on MigrationsEnded: ' . $e->getMessage());
                }
            }
        );
    }
}
