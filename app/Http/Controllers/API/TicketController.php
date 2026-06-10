<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\VehicleAssignment;
use App\Models\TicketUpdate;
use App\Models\TicketAttachment;
use App\Models\Project;
use App\Models\VehicleType;
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
            'ticket' => $ticket
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'ticket_type' => 'required|in:vehicle_support',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'company_id' => 'nullable|integer',
            'project_name' => 'nullable|string|max:255',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'trip_start_datetime' => 'required|date|after:now',
            'trip_end_datetime' => 'required|date|after:trip_start_datetime',
            'passenger_count' => 'required|integer|min:1',
            'trip_purpose' => 'required|string',
            'trip_locations' => 'required|array|min:1',
            'trip_locations.*.start' => 'required|string|max:255',
            'trip_locations.*.end' => 'required|string|max:255',
            'trip_locations.*.start_lat' => 'nullable|numeric|between:-90,90',
            'trip_locations.*.start_lng' => 'nullable|numeric|between:-180,180',
            'trip_locations.*.end_lat' => 'nullable|numeric|between:-90,90',
            'trip_locations.*.end_lng' => 'nullable|numeric|between:-180,180',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file',
        ]);

        $user = User::where('email', $validated['email'])->first();
        $validated['user_id'] = $user->id;

        // Handle trip locations - convert to JSON format
        if ($request->filled('trip_locations')) {
            $tripLocations = [];
            $tripLocationCoordinates = [];
            foreach ($request->trip_locations as $index => $location) {
                $startCoordinates = $this->coordinatesFromLocation($location, 'start');
                $endCoordinates = $this->coordinatesFromLocation($location, 'end');

                $tripLocations[] = [
                    'start' => $location['start'],
                    'end' => $location['end'],
                    'stop_order' => $index + 1
                ];

                $tripLocationCoordinates[] = [
                    'start' => $startCoordinates,
                    'end' => $endCoordinates,
                    'stop_order' => $index + 1
                ];
            }
            $validated['trip_location_details'] = $tripLocations;
            $validated['trip_location_coordinates'] = $tripLocationCoordinates;
            unset($validated['trip_locations']);
        }

        // Remove parameters that are not table columns
        unset($validated['email']);
        unset($validated['attachments']);

        $ticket = Ticket::create($validated);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket_attachments', 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Create initial update
        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'update_message' => 'Ticket created',
            'update_type' => 'system',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], Response::HTTP_CREATED);
    }

    public function getDropdowns()
    {
        $companies = [
            ['id' => 1, 'name' => 'SComm'],
            ['id' => 2, 'name' => 'STL'],
        ];

        $projects = Project::where('status', 1)
            ->select('id', 'name')
            ->get();

        $vehicleTypes = VehicleType::select('id', 'type_name as name')
            ->get();

        return response()->json([
            'success' => true,
            'companies' => $companies,
            'projects' => $projects,
            'vehicle_types' => $vehicleTypes,
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

    private function coordinatesFromLocation(array $location, string $point): array
    {
        $latitude = $location["{$point}_lat"] ?? null;
        $longitude = $location["{$point}_lng"] ?? null;

        if (($latitude === null || $longitude === null) && isset($location[$point])) {
            $parsedCoordinates = $this->parseCoordinatesFromText($location[$point]);
            $latitude ??= $parsedCoordinates['latitude'];
            $longitude ??= $parsedCoordinates['longitude'];
        }

        return [
            'latitude' => $latitude !== null ? (float) $latitude : null,
            'longitude' => $longitude !== null ? (float) $longitude : null,
        ];
    }

    private function parseCoordinatesFromText(?string $locationText): array
    {
        if ($locationText && preg_match('/Lat:\s*(-?\d+(?:\.\d+)?).*Lng:\s*(-?\d+(?:\.\d+)?)/i', $locationText, $matches)) {
            return [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ];
        }

        return [
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
