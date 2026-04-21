<?php

namespace App\Http\Controllers\TicketManagement;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleAssignment;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleAssignmentController extends Controller
{
    /**
     * Get available vehicles and drivers for assignment modal
     */
    public function getAvailableResources(Request $request)
    {
        $ticketId = $request->ticket_id;
        
        $ticket = Ticket::findOrFail($ticketId);

        // Get all vehicles with their current status
        $vehicles = Vehicle::with([
            'vehicleType',
            'currentAssignment.ticket',
            'currentMaintenance',
            'upcomingAssignments' => function($query) {
                $query->limit(3);
            }
        ])->get()->map(function($vehicle) use ($ticket) {
            $status = $vehicle->getCurrentStatus();
            
            return [
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'vehicle_type' => $vehicle->vehicleType->type_name ?? 'N/A',
                'seating_capacity' => $vehicle->seating_capacity,
                'ownership' => $vehicle->getOwnershipType(),
                'color' => $vehicle->color,
                'is_available' => $vehicle->isAvailable(),
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
        ])->get()->map(function($driver) {
            $status = $driver->getCurrentStatus();
            
            return [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
                'email' => $driver->email,
                'office_location' => $driver->office_location,
                'job_location' => $driver->job_location,
                'image_path' => $driver->image_path,
                'employment_contract' => $driver->employment_contract,
                'is_available' => $driver->isAvailable(),
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
            ],
        ]);
    }

    /**
     * Assign vehicle and driver to ticket
     */
    public function assignToTicket(Request $request)
    {   
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
        ]);
        $ticket = Ticket::findOrFail($validated['ticket_id']);
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $driver = Driver::findOrFail($validated['driver_id']);

        // Check if vehicle is available
        if (!$vehicle->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is not available for the selected time period.'
            ], 422);
        }

        // Check if driver is available
        if (!$driver->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Driver is not available for the selected time period.'
            ], 422);
        }

        // Check for conflicts
        $vehicleConflict = $this->checkVehicleConflict($validated['vehicle_id'], $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);
        if ($vehicleConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle has a conflicting assignment during this time period.'
            ], 422);
        }

        $driverConflict = $this->checkDriverConflict($validated['driver_id'], $ticket->trip_start_datetime, $ticket->trip_end_datetime, $ticket->id);
        if ($driverConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Driver has a conflicting assignment during this time period.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create assignment record
            VehicleAssignment::create([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'ticket_id' => $validated['ticket_id'],
                'start_datetime' => $ticket->trip_start_datetime,
                'end_datetime' => $ticket->trip_end_datetime,
                'status' => $ticket->trip_start_datetime <= now() ? 'active' : 'scheduled',
            ]);

            // Update ticket
            $ticket->update([
                'assigned_driver_id' => $validated['driver_id'],
                'assigned_vehicle_id' => $validated['vehicle_id'],
                'status' => 'assigned',
            ]);

            // Create ticket update
            TicketUpdate::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'update_message' => "Vehicle {$vehicle->registration_number} ({$vehicle->brand} {$vehicle->model}) and driver {$driver->full_name} assigned to this trip",
                'update_type' => 'assignment',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle and driver assigned successfully!',
                'assignment' => [
                    'vehicle' => "{$vehicle->brand} {$vehicle->model} ({$vehicle->registration_number})",
                    'driver' => $driver->full_name,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign vehicle and driver. Please try again.'
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
            });

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
            });

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
}
