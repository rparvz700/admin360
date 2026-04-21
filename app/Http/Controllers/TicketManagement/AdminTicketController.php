<?php

namespace App\Http\Controllers\TicketManagement;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'assignedTo', 'vehicleType', 'asset', 'assetCategory']);

        if ($request->filled('ticket_type')) {
            $query->where('ticket_type', $request->ticket_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(20);

        return view('TicketManagement.admin_index', compact('tickets'));
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

        return view('TicketManagement.admin_show', compact('ticket', 'admins', 'drivers', 'vehicles'));
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
