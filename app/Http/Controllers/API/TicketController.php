<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\VehicleAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $tickets = Ticket::with([
                'vehicleType',
                'latestVehicleAssignment.driver',
                'latestVehicleAssignment.vehicle.vehicleType',
            ])
            ->where('user_id', $user->id)
            ->where('ticket_type', 'vehicle_support')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'tickets' => $tickets->map(fn (Ticket $ticket) => $this->formatTicket($ticket))->values(),
        ]);
    }

    public function show(int $ticketId)
    {
        $ticket = Ticket::with([
                'user',
                'vehicleType',
                'latestVehicleAssignment.driver',
                'latestVehicleAssignment.vehicle.vehicleType',
            ])
            ->where('ticket_type', 'vehicle_support')
            ->find($ticketId);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'user' => $ticket->user ? [
                'id' => $ticket->user->id,
                'name' => $ticket->user->name,
                'email' => $ticket->user->email,
            ] : null,
            'ticket' => $this->formatTicket($ticket),
        ]);
    }

    public function storeLocationTracking(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $validated = $request->validate([
            'latitude' => 'required_without:locations|numeric|between:-90,90',
            'longitude' => 'required_without:locations|numeric|between:-180,180',
            'recorded_at' => 'nullable|date',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|min:0|max:360',
            'accuracy' => 'nullable|numeric|min:0',
            'battery_level' => 'nullable|numeric|min:0|max:100',
            'metadata' => 'nullable|array',
            'locations' => 'sometimes|array|min:1',
            'locations.*.latitude' => 'required_with:locations|numeric|between:-90,90',
            'locations.*.longitude' => 'required_with:locations|numeric|between:-180,180',
            'locations.*.recorded_at' => 'nullable|date',
            'locations.*.speed' => 'nullable|numeric|min:0',
            'locations.*.heading' => 'nullable|numeric|min:0|max:360',
            'locations.*.accuracy' => 'nullable|numeric|min:0',
            'locations.*.battery_level' => 'nullable|numeric|min:0|max:100',
            'locations.*.metadata' => 'nullable|array',
        ]);

        $newLocations = isset($validated['locations'])
            ? collect($validated['locations'])->map(fn (array $location) => $this->formatTrackingPoint($location))->all()
            : [$this->formatTrackingPoint($validated)];

        $currentTracking = $vehicleAssignment->location_tracking ?? [];
        $vehicleAssignment->update([
            'location_tracking' => array_values(array_merge($currentTracking, $newLocations)),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location tracking saved successfully.',
            'assignment_id' => $vehicleAssignment->id,
            'tracking_count' => count($vehicleAssignment->fresh()->location_tracking ?? []),
            'locations' => $newLocations,
        ]);
    }

    public function startTrip(VehicleAssignment $vehicleAssignment)
    {
        if (in_array($vehicleAssignment->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Trip cannot be started from the current status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $vehicleAssignment->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trip started successfully.',
            'assignment' => $this->formatAssignment($vehicleAssignment->fresh(['driver', 'vehicle.vehicleType'])),
        ]);
    }

    public function completeTrip(VehicleAssignment $vehicleAssignment)
    {
        if ($vehicleAssignment->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Trip is already completed.',
                'assignment' => $this->formatAssignment($vehicleAssignment->loadMissing(['driver', 'vehicle.vehicleType'])),
            ]);
        }

        if ($vehicleAssignment->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled trip cannot be completed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $vehicleAssignment->update([
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trip completed successfully.',
            'assignment' => $this->formatAssignment($vehicleAssignment->fresh(['driver', 'vehicle.vehicleType'])),
        ]);
    }

    private function formatTicket(Ticket $ticket): array
    {
        $assignment = $ticket->latestVehicleAssignment;

        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'ticket_type' => $ticket->ticket_type,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'vehicle_type' => $ticket->vehicleType ? [
                'id' => $ticket->vehicleType->id,
                'name' => $ticket->vehicleType->type_name,
            ] : null,
            'trip_start_datetime' => optional($ticket->trip_start_datetime)->toDateTimeString(),
            'trip_end_datetime' => optional($ticket->trip_end_datetime)->toDateTimeString(),
            'passenger_count' => $ticket->passenger_count,
            'trip_purpose' => $ticket->trip_purpose,
            'locations' => $this->formatLocations($ticket),
            'assignment_status' => $assignment ? $assignment->status : 'vehicle_not_assigned',
            'vehicle_assigned' => (bool) $assignment,
            'assignment' => $assignment ? $this->formatAssignment($assignment) : null,
            'created_at' => optional($ticket->created_at)->toDateTimeString(),
        ];
    }

    private function formatLocations(Ticket $ticket): array
    {
        $locations = $ticket->trip_location_details ?? [];
        $coordinates = $ticket->trip_location_coordinates ?? [];

        return collect($locations)->map(function (array $location, int $index) use ($coordinates) {
            $coordinate = $coordinates[$index] ?? [];

            return [
                'stop_order' => $location['stop_order'] ?? $coordinate['stop_order'] ?? $index + 1,
                'start' => [
                    'address' => $location['start'] ?? null,
                    'latitude' => $coordinate['start']['latitude'] ?? null,
                    'longitude' => $coordinate['start']['longitude'] ?? null,
                ],
                'end' => [
                    'address' => $location['end'] ?? null,
                    'latitude' => $coordinate['end']['latitude'] ?? null,
                    'longitude' => $coordinate['end']['longitude'] ?? null,
                ],
            ];
        })->values()->all();
    }

    private function formatAssignment(VehicleAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'status' => $assignment->status,
            'start_datetime' => optional($assignment->start_datetime)->toDateTimeString(),
            'end_datetime' => optional($assignment->end_datetime)->toDateTimeString(),
            'tracking_count' => count($assignment->location_tracking ?? []),
            'driver' => $assignment->driver ? [
                'id' => $assignment->driver->id,
                'name' => $assignment->driver->full_name,
                'phone' => $assignment->driver->phone,
                'email' => $assignment->driver->email,
                'image_path' => $assignment->driver->image_path,
            ] : null,
            'vehicle' => $assignment->vehicle ? [
                'id' => $assignment->vehicle->id,
                'registration_number' => $assignment->vehicle->registration_number,
                'brand' => $assignment->vehicle->brand,
                'model' => $assignment->vehicle->model,
                'color' => $assignment->vehicle->color,
                'seating_capacity' => $assignment->vehicle->seating_capacity,
                'vehicle_type' => $assignment->vehicle->vehicleType ? [
                    'id' => $assignment->vehicle->vehicleType->id,
                    'name' => $assignment->vehicle->vehicleType->type_name,
                ] : null,
            ] : null,
        ];
    }

    private function formatTrackingPoint(array $location): array
    {
        return [
            'latitude' => (float) $location['latitude'],
            'longitude' => (float) $location['longitude'],
            'recorded_at' => isset($location['recorded_at'])
                ? Carbon::parse($location['recorded_at'])->toDateTimeString()
                : now()->toDateTimeString(),
            'speed' => isset($location['speed']) ? (float) $location['speed'] : null,
            'heading' => isset($location['heading']) ? (float) $location['heading'] : null,
            'accuracy' => isset($location['accuracy']) ? (float) $location['accuracy'] : null,
            'battery_level' => isset($location['battery_level']) ? (float) $location['battery_level'] : null,
            'metadata' => $location['metadata'] ?? null,
        ];
    }
}
