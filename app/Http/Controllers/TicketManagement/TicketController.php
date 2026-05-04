<?php

namespace App\Http\Controllers\TicketManagement;

use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\TicketAttachment;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\VehicleType;
use App\Models\PropertiesFloor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with the base query, eager load relationships
            $query = Ticket::with(['user', 'assignedTo', 'vehicleType', 'asset', 'assetCategory'])
                ->where('user_id', Auth::id()); // Filter by logged-in user

            // Apply filters from the request (these come from DataTables AJAX 'data' function)
            if ($request->filled('ticket_type')) {
                $query->where('ticket_type', $request->ticket_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                // Ticket Number with link
                ->addColumn('ticket_number_link', function ($ticket) {
                    return '<a href="' . route('tickets.show', $ticket) . '" class="text-decoration-none">' .
                           $ticket->ticket_number .
                           '</a>';
                })
                // Ticket Type with badge
                ->addColumn('ticket_type_badge', function ($ticket) {
                    return '<span class="badge bg-secondary">' .
                           ucwords(str_replace('_', ' ', $ticket->ticket_type)) .
                           '</span>';
                })
                // Title (direct column)
                ->editColumn('title', function ($ticket) {
                    return $ticket->title;
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
                // Created At formatted
                ->editColumn('created_at', function ($ticket) {
                    return $ticket->created_at->format('M d, Y H:i');
                })
                // Actions column
                ->addColumn('actions', function ($ticket) {
                    // Assuming you have a partial for ticket actions. Adjust path if needed.
                    return '<a href="' . route('tickets.show', $ticket) . '" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>';
                })
                // Specify which columns contain HTML and should not be escaped
                ->rawColumns(['ticket_number_link', 'ticket_type_badge', 'priority_badge', 'status_badge', 'actions'])
                // For custom filtering of related data, if needed for global search
                // For example, if you wanted to search by vehicleType name, you'd add:
                // ->filterColumn('vehicle_type_name', function($query, $keyword) {
                //     $query->whereHas('vehicleType', function($q) use ($keyword) {
                //         $q->where('name', 'like', "%{$keyword}%");
                //     });
                // })
                ->make(true);
        }

        // For initial page load, return the view without data.
        // The DataTables JS will then make the AJAX request for data.
        return view('TicketManagement.index');
    }

    public function create()
    {
        $vehicleTypes = VehicleType::all();
        $assetCategories = AssetCategory::all();
        $floors = PropertiesFloor::with('building')->get();
        $assets = Asset::with(['category', 'floor'])->where('status', 'active')->get();
        $companies = array(array("name"=>"SComm","id"=>1),array("name"=>"STL","id"=>2)); // Placeholder for company data
        $projects = array(array("project_name"=>"Project Alpha","id"=>1),array("project_name"=>"Project Beta","id"=>2)); // Placeholder for project data    
        //dd($companies,$projects);
        return view('TicketManagement.create', compact('vehicleTypes', 'assetCategories', 'floors', 'assets', 'companies', 'projects'));
    }

    public function store(Request $request)
    {   
        $rules = [
            'ticket_type' => 'required|in:vehicle_support,asset_request,asset_repair',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'company_id' => 'nullable|integer',
            'project_name' => 'nullable|string|max:255',
        ];

        // Type-specific validation
        if ($request->ticket_type === 'vehicle_support') {
            $rules += [
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'trip_start_datetime' => 'required|date|after:now',
                'trip_end_datetime' => 'required|date|after:trip_start_datetime',
                'passenger_count' => 'required|integer|min:1',
                'trip_purpose' => 'required|string',
                'trip_locations' => 'required|array|min:1',
                'trip_locations.*.start' => 'required|string|max:255',
                'trip_locations.*.end' => 'required|string|max:255',
            ];
        } elseif ($request->ticket_type === 'asset_request') {
            $rules += [
                'asset_category_id' => 'required|exists:asset_categories,id',
                'requested_asset_name' => 'required|string',
                'asset_specifications' => 'nullable|string',
                'floor_id' => 'required|exists:properties_floors,id',
                'location_within_floor' => 'required|string',
            ];
        } elseif ($request->ticket_type === 'asset_repair') {
            $rules += [
                'asset_id' => 'required|exists:assets,id',
                'repair_floor_id' => 'required|exists:properties_floors,id',
                'repair_location_within_floor' => 'required|string',
            ];
        }

        $validated = $request->validate($rules);
        $validated['user_id'] = Auth::id();
        
        // Handle repair-specific field names
        if ($request->ticket_type === 'asset_repair') {
            if ($request->filled('repair_floor_id')) {
                $validated['floor_id'] = $request->repair_floor_id;
                unset($validated['repair_floor_id']);
            }
            if ($request->filled('repair_location_within_floor')) {
                $validated['location_within_floor'] = $request->repair_location_within_floor;
                unset($validated['repair_location_within_floor']);
            }
        }
        
        // Handle trip locations - convert to JSON format
        if ($request->ticket_type === 'vehicle_support' && $request->filled('trip_locations')) {
            $tripLocations = [];
            foreach ($request->trip_locations as $index => $location) {
                $tripLocations[] = [
                    'start' => $location['start'],
                    'end' => $location['end'],
                    'stop_order' => $index + 1
                ];
            }
            $validated['trip_location_details'] = $tripLocations;
            unset($validated['trip_locations']);
        }

        $ticket = Ticket::create($validated);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket_attachments', 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
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
            'user_id' => Auth::id(),
            'update_message' => 'Ticket created',
            'update_type' => 'system',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully!');
    }

    public function show(Ticket $ticket)
    {
        // Ensure user can only view their own tickets (unless admin)
        if ($ticket->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

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

        return view('TicketManagement.show', compact('ticket'));
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

    public function cancel(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        if (in_array($ticket->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel a completed or already cancelled ticket.');
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => 'cancelled']);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'update_message' => 'Ticket cancelled by user',
            'update_type' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => 'cancelled',
        ]);

        return back()->with('success', 'Ticket cancelled successfully!');
    }
}
