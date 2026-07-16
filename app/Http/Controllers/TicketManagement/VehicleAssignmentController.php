<?php

namespace App\Http\Controllers\TicketManagement;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleAssignment;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\VehicleOperationalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VehicleAssignmentController extends Controller
{
    /**
     * Get available vehicles and drivers for assignment modal
     */
    public function getAvailableResources(Request $request)
    {
        $ticketId = $request->ticket_id;
        
        $ticket = Ticket::findOrFail($ticketId);

        // Fetch existing assignment to ignore availability issues for currently assigned resources
        $existingAssignment = VehicleAssignment::where('ticket_id', $ticket->id)->first();
        $existingVehicleId = $existingAssignment->vehicle_id ?? null;
        $existingDriverId = $existingAssignment->driver_id ?? null;

        // Get all vehicles with their current status
        $vehicles = Vehicle::with([
            'vehicleType',
            'currentAssignment.ticket',
            'currentMaintenance',
            'upcomingAssignments' => function($query) {
                $query->limit(3);
            }
        ])->get()->map(function($vehicle) use ($ticket, $existingVehicleId) {
            $status = $vehicle->getCurrentStatus();
            
            $isAvailable = $vehicle->isAvailable();
            if ($existingVehicleId && $vehicle->id == $existingVehicleId) {
                $isAvailable = true;
            }

            return [
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'vehicle_type' => $vehicle->vehicleType->type_name ?? 'N/A',
                'seating_capacity' => $vehicle->seating_capacity,
                'ownership' => $vehicle->getOwnershipType(),
                'color' => $vehicle->color,
                'is_available' => $isAvailable,
                'status' => $status['status'],
                'status_label' => $status['label'],
                'status_color' => $status['color'],
                'current_assignment' => $status['details'] ? [
                    'ticket_number' => $status['details']->ticket->ticket_number ?? 'N/A',
                    'end_datetime' => $status['details']->end_datetime,
                    'time_remaining' => $status['details']->time_remaining ?? $status['details']->estimated_time_remaining ?? 0,
                ] : null,
                'upcoming_count' => $vehicle->upcomingAssignments->count(),
                // Check if this vehicle matches ticket requirements
                'matches_requirement' => $ticket->vehicle_type_id == $vehicle->vehicle_type_id,
            ];
        });

        // Get all drivers with their current status
        $drivers = Driver::with([
            'currentAssignment.ticket',
            'availability',
            'upcomingAssignments' => function($query) {
                $query->limit(3);
            }
        ])->get()->map(function($driver) use ($existingDriverId) {
            $status = $driver->getCurrentStatus();
            
            $isAvailable = $driver->isAvailable();
            if ($existingDriverId && $driver->id == $existingDriverId) {
                $isAvailable = true;
            }

            return [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
                'email' => $driver->email,
                'office_location' => $driver->office_location,
                'job_location' => $driver->job_location,
                'image_path' => $driver->image_path,
                'employment_contract' => $driver->employment_contract,
                'is_available' => $isAvailable,
                'status' => $status['status'],
                'status_label' => $status['label'],
                'status_color' => $status['color'],
                'current_assignment' => $status['details'] && $status['status'] == 'on_assignment' ? [
                    'ticket_number' => $status['details']->ticket->ticket_number ?? 'N/A',
                    'end_datetime' => $status['details']->end_datetime,
                    'time_remaining' => $status['details']->time_remaining,
                ] : null,
                'unavailable_until' => $status['details'] && $status['status'] != 'on_assignment' ? 
                    $status['details']->unavailable_until : null,
                'upcoming_count' => $driver->upcomingAssignments->count(),
            ];
        });
        
        return response()->json([
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'start_datetime' => $ticket->trip_start_datetime,
                'end_datetime' => $ticket->trip_end_datetime,
                'passenger_count' => $ticket->passenger_count,
                'vehicle_type' => $ticket->vehicleType->type_name ?? 'N/A',
                'assigned_vehicle_id' => $ticket->assigned_vehicle_id,
                'assigned_driver_id' => $ticket->assigned_driver_id,
            ],
        ]);
    }


    /**
     * Assign vehicle and driver to ticket
     */
    // public function assignToTicket(Request $request)
    // {   
    //     $validated = $request->validate([
    //         'ticket_id' => 'required|exists:tickets,id',
    //         'vehicle_id' => 'required|exists:vehicles,id',
    //         'driver_id' => 'required|exists:drivers,id',
    //     ]);
    //     $ticket = Ticket::findOrFail($validated['ticket_id']);
    //     $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
    //     $driver = Driver::findOrFail($validated['driver_id']);

    //     // Check if vehicle is available
    //     if (!$vehicle->isAvailable()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Vehicle is not available for the selected time period.'
    //         ], 422);
    //     }

    //     // Check if driver is available
    //     if (!$driver->isAvailable()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Driver is not available for the selected time period.'
    //         ], 422);
    //     }

    //     // Check for conflicts
    //     $vehicleConflict = $this->checkVehicleConflict($validated['vehicle_id'], $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);
    //     if ($vehicleConflict) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Vehicle has a conflicting assignment during this time period.'
    //         ], 422);
    //     }

    //     $driverConflict = $this->checkDriverConflict($validated['driver_id'], $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);
    //     if ($driverConflict) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Driver has a conflicting assignment during this time period.'
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Create assignment record
    //         // VehicleAssignment::create([
    //         //     'vehicle_id' => $validated['vehicle_id'],
    //         //     'driver_id' => $validated['driver_id'],
    //         //     'ticket_id' => $validated['ticket_id'],
    //         //     'start_datetime' => $ticket->trip_start_datetime,
    //         //     'end_datetime' => $ticket->trip_end_datetime,
    //         //     'status' => $ticket->trip_start_datetime <= now() ? 'active' : 'scheduled',
    //         // ]);

    //         VehicleAssignment::updateOrCreate(
    //             [
    //                 'ticket_id' => $validated['ticket_id'],
    //             ],
    //             [
    //                 'vehicle_id' => $validated['vehicle_id'],
    //                 'driver_id' => $validated['driver_id'],
    //                 'start_datetime' => $ticket->trip_start_datetime,
    //                 'end_datetime' => $ticket->trip_end_datetime,
    //                 'status' => $ticket->trip_start_datetime <= now() ? 'active' : 'scheduled',
    //             ]
    //         );
    //         // Update ticket
    //         $ticket->update([
    //             'assigned_driver_id' => (int)$validated['driver_id'],
    //             'assigned_vehicle_id' => (int)$validated['vehicle_id'],
    //             'status' => 'assigned',
    //         ]);

    //         // Create ticket update
    //         TicketUpdate::create([
    //             'ticket_id' => $ticket->id,
    //             'user_id' => Auth::id(),
    //             'update_message' => "Vehicle {$vehicle->registration_number} ({$vehicle->brand} {$vehicle->model}) and driver {$driver->full_name} assigned to this trip",
    //             'update_type' => 'assignment',
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Vehicle and driver assigned successfully!',
    //             'assignment' => [
    //                 'vehicle' => "{$vehicle->brand} {$vehicle->model} ({$vehicle->registration_number})",
    //                 'driver' => $driver->full_name,
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to assign vehicle and driver. Please try again.'
    //         ], 500);
    //     }
    // }


    public function assignToTicket(Request $request)
    {
        // dd($request->all());
        // 1. Validation for both regular and manual assignments
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            // 'is_manual' => 'boolean',

            // Manual vehicle data validation
            'manual_vehicle_data.vehicle_type' => 'required_if:vehicle_id,manual|string|max:255',
            'manual_vehicle_data.registration_number' => 'required_if:vehicle_id,manual|string|max:255',
            'manual_vehicle_data.brand' => 'required_if:vehicle_id,manual|string|max:255',
            'manual_vehicle_data.model' => 'required_if:vehicle_id,manual|string|max:255',
            'manual_vehicle_data.color' => 'nullable|string|max:255',
            'manual_vehicle_data.seating_capacity' => 'required_if:vehicle_id,manual|integer|min:1',
            'manual_vehicle_data.rental_type' => 'required_if:vehicle_id,manual|string|in:daily,hourly,trip',
            'manual_vehicle_data.rental_cost' => 'nullable|numeric|min:0',
            'manual_vehicle_data.rental_company' => 'nullable|string|max:255',
            'manual_vehicle_data.notes' => 'nullable|string',

            // Manual driver data validation
            'manual_driver_data.name' => 'required_if:driver_id,manual|string|max:255',
            'manual_driver_data.phone' => 'required_if:driver_id,manual|string|max:255',
            'manual_driver_data.email' => 'nullable|email|max:255',
            // 'manual_driver_data.license_number' => 'required_if:driver_id,manual|string|max:255',
            // 'manual_driver_data.license_expiry' => 'nullable|date',
            'manual_driver_data.nid' => 'nullable|string|max:255',
            'manual_driver_data.blood_group' => 'nullable|string|max:10',
            'manual_driver_data.emergency_contact' => 'nullable|string|max:255',
            'manual_driver_data.notes' => 'nullable|string',
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);
        $vehicleIdToAssign = $validated['vehicle_id'];
        $driverIdToAssign = $validated['driver_id'];
        $vehicle = null; // Initialize Vehicle model instance
        $driver = null;  // Initialize Driver model instance
        $isManual = (bool)($validated['is_manual'] ?? false);

        DB::beginTransaction();
        try {
            // 2. Handle manual vehicle creation
            if ($vehicleIdToAssign === 'manual') {
                
                $vehicle = Vehicle::create([
                    'registration_number' => $validated['manual_vehicle_data']['registration_number'],
                    'vehicle_type_id' => $validated['manual_vehicle_data']['vehicle_type'],
                    'brand' => $validated['manual_vehicle_data']['brand'],
                    'model' => $validated['manual_vehicle_data']['model'],
                    'color' => $validated['manual_vehicle_data']['color'] ?? null,
                    'seating_capacity' => $validated['manual_vehicle_data']['seating_capacity'],
                    'isRented' => true, // Manual entries are always 'Rented'
                    // 'rental_type' => $validated['manual_vehicle_data']['rental_type'],
                    // 'rental_cost' => $validated['manual_vehicle_data']['rental_cost'] ?? 0,
                    // 'rental_company' => $validated['manual_vehicle_data']['rental_company'] ?? null,
                    // 'status' => 'on_assignment', 
                    // 'notes' => $validated['manual_vehicle_data']['notes'] ?? null,
                    'is_manual_entry' => true, // Flag as manual entry
                ]);
                $ticket->update([
                    'vehicle_id' => $validated['manual_vehicle_data']['rental_company'] ?? null,
                    'cost' => $validated['manual_vehicle_data']['rental_cost'] ?? null
                ]);
                $vehicleIdToAssign = $vehicle->id;
            } else {
                $vehicle = Vehicle::findOrFail($vehicleIdToAssign);
            }

            // 3. Handle manual driver creation
            if ($driverIdToAssign === 'manual') {
                $driver = Driver::create([
                    'name' => $validated['manual_driver_data']['name'],
                    'phone' => $validated['manual_driver_data']['phone'],
                    'email' => $validated['manual_driver_data']['email'] ?? null,
                    // 'license_number' => $validated['manual_driver_data']['license_number'],
                    // 'license_expiry_date' => $validated['manual_driver_data']['license_expiry'] ?? null,
                    'nid' => $validated['manual_driver_data']['nid'] ?? null,
                    'blood_group' => $validated['manual_driver_data']['blood_group'] ?? null,
                    'emergency_contact' => $validated['manual_driver_data']['emergency_contact'] ?? null,
                    // 'employment_contract' => 'Ad-hoc', // Manual entries are always 'Ad-hoc'
                    // 'status' => 'on_assignment', // Initially set to on assignment
                    // 'notes' => $validated['manual_driver_data']['notes'] ?? null,
                    'is_manual_entry' => true, // Flag as manual entry
                ]);
                $driverIdToAssign = $driver->id;
            } else {
                $driver = Driver::findOrFail($driverIdToAssign);
            }

            // 4. Check availability for non-manual entries and existing assignments
            // Manual entries are assumed available upon creation
            // if (!$isManual) { // Only check availability for existing system vehicles/drivers
            //      // Check if vehicle is available. Pass ticket details for more accurate check.
            //     if (!$vehicle->isAvailableForPeriod($ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id)) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Vehicle is not available for the selected time period.'
            //         ], 422);
            //     }

            //     // Check if driver is available. Pass ticket details for more accurate check.
            //     if (!$driver->isAvailableForPeriod($ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id)) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Driver is not available for the selected time period.'
            //         ], 422);
            //     }
            // }


            // 5. Check for conflicts (excluding the current ticket's existing assignment, if any)
            $vehicleConflict = $this->checkVehicleConflict($vehicleIdToAssign, $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);

            if ($vehicleConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle has a conflicting assignment during this time period with another ticket.'
                ], 422);
            }

            $driverConflict = $this->checkDriverConflict($driverIdToAssign, $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);
            if ($driverConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver has a conflicting assignment during this time period with another ticket.'
                ], 422);
            }

            // Determine if it's a new assignment or a reassignment
            $isReassignment = VehicleAssignment::where('ticket_id', $ticket->id)->exists();
            $actionWord = $isReassignment ? 'reassigned' : 'assigned';


            // 6. Update or Create assignment record
            VehicleAssignment::updateOrCreate(
                ['ticket_id' => $validated['ticket_id']], // Match on ticket_id
                [
                    'vehicle_id' => $vehicleIdToAssign,
                    'driver_id' => $driverIdToAssign,
                    'start_datetime' => $ticket->trip_start_datetime,
                    'end_datetime' => $ticket->trip_end_datetime,
                    'status' => $ticket->trip_start_datetime <= now() ? 'active' : 'scheduled',
                    // Consider adding 'is_manual_entry' here too if you want to store it in assignments
                ]
            );

            // 7. Update ticket
            $ticket->update([
                'assigned_driver_id' => $driverIdToAssign,
                'assigned_vehicle_id' => $vehicleIdToAssign,
                // 'status' => 'assigned', // Status is 'assigned' regardless of start time
            ]);

            // 8. Create ticket update log
            TicketUpdate::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'update_message' => "Vehicle {$vehicle->registration_number} ({$vehicle->brand} {$vehicle->model}) and driver {$driver->name} {$actionWord} to this trip",
                'update_type' => 'assignment',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Vehicle and driver {$actionWord} successfully!",
                'assignment' => [
                    'vehicle' => "{$vehicle->brand} {$vehicle->model} ({$vehicle->registration_number})",
                    'driver' => $driver->name,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception for debugging
            Log::error('Assignment failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign vehicle and driver. Please try again. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if vehicle has conflicting assignments
     */
    private function checkVehicleConflict($vehicleId, $startDateTime, $endDateTime, $excludeTicketId = null)
    {
        $query = VehicleAssignment::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where(function($q) use ($startDateTime, $endDateTime) {
                $q->whereBetween('start_datetime', [$startDateTime, $endDateTime])
                  ->orWhereBetween('end_datetime', [$startDateTime, $endDateTime])
                  ->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                      $q2->where('start_datetime', '<=', $startDateTime)
                         ->where('end_datetime', '>=', $endDateTime);
                  });
            })
            ->whereNotIn('status', ['completed', 'cancelled']);

        if ($excludeTicketId) {
            $query->where('ticket_id', '!=', $excludeTicketId);
        }

        return $query->exists();
    }

    /**
     * Check if driver has conflicting assignments
     */
    private function checkDriverConflict($driverId, $startDateTime, $endDateTime, $excludeTicketId = null)
    {
        $query = VehicleAssignment::where('driver_id', $driverId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where(function($q) use ($startDateTime, $endDateTime) {
                $q->whereBetween('start_datetime', [$startDateTime, $endDateTime])
                  ->orWhereBetween('end_datetime', [$startDateTime, $endDateTime])
                  ->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                      $q2->where('start_datetime', '<=', $startDateTime)
                         ->where('end_datetime', '>=', $endDateTime);
                  });
            })
            ->whereNotIn('status', ['completed', 'cancelled']);

        if ($excludeTicketId) {
            $query->where('ticket_id', '!=', $excludeTicketId);
        }

        return $query->exists();
    }

    /**
     * Get upcoming assignments for a resource
     */
    public function getResourceSchedule(Request $request)
    {
        $type = $request->type; // 'vehicle' or 'driver'
        $id = $request->id;

        if ($type === 'vehicle') {
            $assignments = VehicleAssignment::where('vehicle_id', $id)
                ->whereIn('status', ['scheduled', 'active'])
                ->with(['ticket', 'driver'])
                ->orderBy('start_datetime')
                ->limit(10)
                ->get();
        } else {
            $assignments = VehicleAssignment::where('driver_id', $id)
                ->whereIn('status', ['scheduled', 'active'])
                ->with(['ticket', 'vehicle'])
                ->orderBy('start_datetime')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'assignments' => $assignments->map(function($assignment) use ($type) {
                return [
                    'ticket_number' => $assignment->ticket->ticket_number,
                    'start_datetime' => $assignment->start_datetime->format('M d, Y H:i'),
                    'end_datetime' => $assignment->end_datetime->format('M d, Y H:i'),
                    'status' => $assignment->status,
                    'duration' => $assignment->start_datetime->diffInHours($assignment->end_datetime) . ' hours',
                    'resource' => $type === 'vehicle' ? 
                        $assignment->driver->full_name : 
                        "{$assignment->vehicle->brand} {$assignment->vehicle->model}",
                ];
            })
        ]);
    }


    public function tripStart($id)
    {
        $assignment = VehicleAssignment::findOrFail($id);

        if ($assignment->status !== 'scheduled') {
            abort(400, 'Trip cannot be started');
        }

        $assignment->update([
            'status' => 'active',
        ]);

        $loggedBy = Auth::id() 
            ?? $assignment->ticket->assigned_to 
            ?? $assignment->ticket->user_id 
            ?? 1;

        VehicleOperationalLog::create([
            'vehicle_id' => $assignment->vehicle_id,
            'log_type' => 'assignment',
            'assigned_department' => $assignment->ticket->project_name ?? null,
            'assigned_user_id' => $assignment->ticket->assigned_to ?? $assignment->ticket->user_id ?? null,
            'meter_reading' => $assignment->start_odo_meter ?? 0,
            'vehicle_status' => 'active',
            'remarks' => 'Trip started (Web)',
            'logged_by' => $loggedBy,
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Trip started successfully');
    }


    public function tripCompleted(Request $request, $id)
    {
        $assignment = VehicleAssignment::findOrFail($id);

        if ($assignment->status !== 'active') {
            abort(400, 'Trip cannot be complete');
        }

        $remarks = $request->input('remarks');

        $assignment->update([
            'status' => 'completed',
            'remarks' => $remarks,
        ]);

        $loggedBy = Auth::id() 
            ?? $assignment->ticket->assigned_to 
            ?? $assignment->ticket->user_id 
            ?? 1;

        VehicleOperationalLog::create([
            'vehicle_id' => $assignment->vehicle_id,
            'log_type' => 'assignment',
            'assigned_department' => $assignment->ticket->project_name ?? null,
            'assigned_user_id' => $assignment->ticket->assigned_to ?? $assignment->ticket->user_id ?? null,
            'meter_reading' => $assignment->end_odo_meter ?? $assignment->start_odo_meter ?? 0,
            'vehicle_status' => 'active',
            'remarks' => $remarks ?? 'Trip completed (Web)',
            'logged_by' => $loggedBy,
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Trip completed successfully');
    }
}
