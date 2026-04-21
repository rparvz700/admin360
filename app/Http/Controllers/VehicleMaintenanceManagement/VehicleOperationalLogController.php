<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleOperationalLog;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleOperationalLogController extends Controller
{
    /**
     * Display a listing of operational logs
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $logs = VehicleOperationalLog::with(['vehicle', 'assignedUser', 'logger'])
                ->orderByDesc('logged_at')
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'vehicle' => $log->vehicle->registration_number ?? 'N/A',
                        'log_type' => '<span class="badge bg-' . $log->getLogTypeBadge() . '">' . 
                                     $log->getLogTypeLabel() . '</span>',
                        'logged_at' => $log->logged_at->format('d M Y H:i'),
                        'meter_reading' => $log->meter_reading ? number_format($log->meter_reading) . ' km' : 'N/A',
                        'vehicle_status' => $log->vehicle_status ? 
                            '<span class="badge bg-' . $log->getStatusBadge() . '">' . ucfirst(str_replace('_', ' ', $log->vehicle_status)) . '</span>' : 
                            'N/A',
                        'assigned_to' => $log->log_type === 'assignment' ? 
                            ($log->assignedUser->name ?? $log->assigned_department ?? 'N/A') : 
                            'N/A',
                        'logged_by' => $log->logger->name ?? 'N/A',
                        'actions' => view('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.partials.actions', compact('log'))->render(),
                    ];
                });

            return response()->json(['data' => $logs]);
        }

        return view('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.index');
    }

    /**
     * Show the form for creating a new log
     */
    public function create()
    {
        $vehicles = Vehicle::all();
        $users = User::all();
        
        return view('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.create', compact('vehicles', 'users'));
    }

    /**
     * Store a newly created log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'log_type' => 'required|in:meter_reading,assignment,status_change',
            'assigned_department' => 'nullable|string|max:255',
            'assigned_user_id' => 'nullable|exists:users,id',
            'meter_reading' => 'nullable|integer|min:0',
            'vehicle_status' => 'nullable|in:active,inactive,sold,under_maintenance',
            'remarks' => 'nullable|string',
        ]);

        // Validate required fields based on log type
        if ($validated['log_type'] === 'meter_reading' && !$request->filled('meter_reading')) {
            return back()->withInput()->withErrors(['meter_reading' => 'Meter reading is required for this log type.']);
        }

        if ($validated['log_type'] === 'assignment' && !$request->filled('assigned_department') && !$request->filled('assigned_user_id')) {
            return back()->withInput()->withErrors(['assigned_department' => 'Either department or user assignment is required.']);
        }

        if ($validated['log_type'] === 'status_change' && !$request->filled('vehicle_status')) {
            return back()->withInput()->withErrors(['vehicle_status' => 'Vehicle status is required for this log type.']);
        }

        $validated['logged_by'] = Auth::id();
        $validated['logged_at'] = now();

        VehicleOperationalLog::create($validated);

        return redirect()->route('maintenance.operational-logs.index')
            ->with('success', 'Operational log created successfully.');
    }

    /**
     * Display the specified log
     */
    public function show(VehicleOperationalLog $operationalLog)
    {
        $operationalLog->load(['vehicle', 'assignedUser', 'logger']);
        return view('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.show', compact('operationalLog'));
    }

    

    /**
     * Remove the specified log
     */
    public function destroy(VehicleOperationalLog $operationalLog)
    {
        $operationalLog->delete();

        return redirect()->route('maintenance.operational-logs.index')
            ->with('success', 'Operational log deleted successfully.');
    }

    /**
     * Quick meter reading update
     */
    public function quickMeterReading(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'meter_reading' => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $validated['log_type'] = 'meter_reading';
        $validated['logged_by'] = Auth::id();
        $validated['logged_at'] = now();

        VehicleOperationalLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meter reading updated successfully.',
        ]);
    }
}
