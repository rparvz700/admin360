<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Add new fields first
        if (!Schema::hasColumn('tickets', 'company_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
            });
        }
        
        if (!Schema::hasColumn('tickets', 'project_name')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('project_name')->nullable()->after('company_id');
            });
        }
        
        if (!Schema::hasColumn('tickets', 'trip_location_details')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('trip_location_details')->nullable()->after('trip_end_datetime');
            });
        }
        
        // Step 2: Migrate existing location data to JSON format
        $tickets = DB::table('tickets')
            ->where('ticket_type', 'vehicle_support')
            ->where(function($query) {
                $query->whereNotNull('pickup_location')
                      ->orWhereNotNull('destination');
            })
            ->get();
        
        foreach ($tickets as $ticket) {
            $locationDetails = [
                [
                    'pickup' => $ticket->pickup_location ?? '',
                    'destination' => $ticket->destination ?? '',
                    'stop_order' => 1
                ]
            ];
            
            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['trip_location_details' => json_encode($locationDetails)]);
        }
        
        // Step 3: Drop old columns
        Schema::table('tickets', function (Blueprint $table) {
            // Drop old location fields
            if (Schema::hasColumn('tickets', 'pickup_location')) {
                $table->dropColumn('pickup_location');
            }
            if (Schema::hasColumn('tickets', 'destination')) {
                $table->dropColumn('destination');
            }
        });
        
        // Step 4: Drop assigned driver and vehicle foreign keys and columns
        // Check if foreign keys exist first (PostgreSQL specific)
        $foreignKeys = DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'tickets' 
            AND constraint_type = 'FOREIGN KEY'
            AND constraint_name LIKE '%assigned_driver_id%'
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['assigned_driver_id']);
            });
        }
        
        $foreignKeys = DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'tickets' 
            AND constraint_type = 'FOREIGN KEY'
            AND constraint_name LIKE '%assigned_vehicle_id%'
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['assigned_vehicle_id']);
            });
        }
        
        // Drop the columns
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'assigned_driver_id')) {
                $table->dropColumn('assigned_driver_id');
            }
            if (Schema::hasColumn('tickets', 'assigned_vehicle_id')) {
                $table->dropColumn('assigned_vehicle_id');
            }
        });
    }

    public function down()
    {
        // Restore old structure
        if (!Schema::hasColumn('tickets', 'pickup_location')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('pickup_location')->nullable()->after('trip_end_datetime');
            });
        }
        
        if (!Schema::hasColumn('tickets', 'destination')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('destination')->nullable()->after('pickup_location');
            });
        }
        
        // Migrate JSON data back to separate columns
        $tickets = DB::table('tickets')
            ->where('ticket_type', 'vehicle_support')
            ->whereNotNull('trip_location_details')
            ->get();
        
        foreach ($tickets as $ticket) {
            $locationDetails = json_decode($ticket->trip_location_details, true);
            if (!empty($locationDetails) && isset($locationDetails[0])) {
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update([
                        'pickup_location' => $locationDetails[0]['pickup'] ?? null,
                        'destination' => $locationDetails[0]['destination'] ?? null
                    ]);
            }
        }
        
        if (!Schema::hasColumn('tickets', 'assigned_driver_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            });
        }
        
        if (!Schema::hasColumn('tickets', 'assigned_vehicle_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            });
        }
        
        // Drop new fields
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'trip_location_details')) {
                $table->dropColumn('trip_location_details');
            }
            if (Schema::hasColumn('tickets', 'company_id')) {
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('tickets', 'project_name')) {
                $table->dropColumn('project_name');
            }
        });
    }
};