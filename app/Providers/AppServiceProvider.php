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
        } catch (\Exception $e) {
            // Fail silently if DB is not ready/migrated yet
        }
    }
}
