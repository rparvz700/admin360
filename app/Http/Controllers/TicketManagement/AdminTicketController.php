<?php

namespace App\Http\Controllers\TicketManagement;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class AdminTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:admin-ticket-management');
    }

    public function index(Request $request) // This is for the admin_index view
    {
        if ($request->ajax()) {
            // Start with the base query, eager load relationships
            $query = Ticket::with(['user', 'assignedTo', 'vehicleType', 'asset', 'assetCategory']);

            // Apply filters from the request (these come from DataTables AJAX 'data' function)
            if ($request->filled('ticket_type')) {
                $query->where('ticket_type', $request->ticket_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply the custom global search from the form's 'search' input
            // DataTables' own global search `request('search')['value']` will also be applied
            // to columns that have `name` property set and are `searchable: true`.
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('ticket_number', 'like', "%{$searchTerm}%")
                      ->orWhere('title', 'like', "%{$searchTerm}%")
                      ->orWhereHas('user', function($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            return DataTables::of($query)
                // Ticket Number with link
                ->addColumn('ticket_number_link', function ($ticket) {
                    return '<a href="' . route('admin.tickets.show', $ticket) . '" class="text-decoration-none fw-bold">' .
                           $ticket->ticket_number .
                           '</a>';
                })
                // Ticket Type with badge
                ->addColumn('ticket_type_badge', function ($ticket) {
                    return '<span class="badge bg-secondary">' .
                           ucwords(str_replace('_', ' ', $ticket->ticket_type)) .
                           '</span>';
                })
                // User Name from relationship
                ->addColumn('user_name', function ($ticket) {
                    return $ticket->user->name ?? 'N/A';
                })
                // Title (with Str::limit)
                ->editColumn('title', function ($ticket) {
                    return Str::limit($ticket->title, 30);
                })
                // Priority with badge
                ->addColumn('priority_badge', function ($ticket) {
                    return '<span class="badge bg-' . $ticket->priority_color . '">' .
                           ucfirst($ticket->priority) .
                           '</span>';
                })
                // Status with badge
                ->addColumn('status_badge', function ($ticket) {
                    return '<span class="badge bg-' . $ticket->status_color . '">' .
                           ucwords(str_replace('_', ' ', $ticket->status)) .
                           '</span>';
                })
                // Assigned To Name from relationship
                ->addColumn('assigned_to_name', function ($ticket) {
                    return $ticket->assignedTo->name ?? '<span class="text-muted">Unassigned</span>';
                })
                // Created At formatted
                ->editColumn('created_at', function ($ticket) {
                    return $ticket->created_at->format('M d, Y');
                })
                // Actions column
                ->addColumn('actions', function ($ticket) {
                    // Assuming you have a partial for ticket actions. Adjust path if needed.
                    return '<a href="' . route('admin.tickets.show', $ticket) . '" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>';
                })
                // Specify which columns contain HTML and should not be escaped
                ->rawColumns([
                    'ticket_number_link',
                    'ticket_type_badge',
                    'priority_badge',
                    'status_badge',
                    'assigned_to_name', // If 'Unassigned' is HTML
                    'actions'
                ])
                // Add filterColumn for relationships if DataTables' global search should hit them
                ->filterColumn('user_name', function($query, $keyword) {
                    $query->whereHas('user', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('assigned_to_name', function($query, $keyword) {
                    $query->whereHas('assignedTo', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    })->orWhereDoesntHave('assignedTo')->where(function($q) use ($keyword) {
                        // This allows searching for "unassigned" if the keyword matches.
                        // However, 'Unassigned' is a display value, actual search will be on NULL assigned_to_id
                        if (strtolower($keyword) === 'unassigned' || strtolower($keyword) === 'null') {
                            $q->whereNull('assigned_to_id');
                        }
                    });
                })
                ->make(true);
        }

        // For initial page load, return the view without data.
        // The DataTables JS will then make the AJAX request for data.
        return view('TicketManagement.admin_index');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'user',
            'assignedTo',
            'vehicleType',
            'assignedDriver',
            'assignedVehicle',
            'asset.category',
            'assetCategory',
            'floor.building',
            'updates.user',
            'attachments.user'
        ]);

        $admins = User::where('status', true)->get();
        $drivers = Driver::all();
        $vehicles = Vehicle::where('status', 'active')->get();
        $vehicleTypes = VehicleType::all();
        $vendors = Vendor::where('is_active', true)->get();

        return view('TicketManagement.admin_show', compact('ticket', 'admins', 'drivers', 'vehicles', 'vehicleTypes', 'vendors'));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $assignedUser = User::find($validated['assigned_to']);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'update_message' => "Ticket assigned to {$assignedUser->name}",
            'update_type' => 'assignment',
            'old_status' => $ticket->status,
            'new_status' => 'assigned',
        ]);

        return back()->with('success', 'Ticket assigned successfully!');
    }

    public function assignVehicle(Request $request, Ticket $ticket)
    {
        if ($ticket->ticket_type !== 'vehicle_support') {
            return back()->with('error', 'This operation is only valid for vehicle support tickets.');
        }

        $validated = $request->validate([
            'assigned_driver_id' => 'required|exists:drivers,id',
            'assigned_vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $ticket->update($validated);

        $driver = Driver::find($validated['assigned_driver_id']);
        $vehicle = Vehicle::find($validated['assigned_vehicle_id']);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'update_message' => "Vehicle {$vehicle->registration_number} and driver {$driver->name} assigned to this trip",
            'update_type' => 'assignment',
        ]);

        return back()->with('success', 'Vehicle and driver assigned successfully!');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'update_message' => 'nullable|string',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completed') {
            $ticket->update(['completed_at' => now()]);
        }

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'update_message' => $validated['update_message'] ?? "Status changed from {$oldStatus} to {$validated['status']}",
            'update_type' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);

        return back()->with('success', 'Ticket status updated successfully!');
    }

    public function addUpdate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'update_message' => 'required|string',
        ]);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'update_message' => $request->update_message,
            'update_type' => 'comment',
        ]);

        return back()->with('success', 'Update added successfully!');
    }
}
