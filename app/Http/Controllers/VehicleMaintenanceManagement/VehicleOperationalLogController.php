<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleOperationalLog;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
class VehicleOperationalLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:vehicle-maintenance-management|create-operational-log|edit-operational-log|delete-operational-log', ['only' => ['index', 'show', 'quickMeterReading']]);
        $this->middleware('permission:create-operational-log', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-operational-log', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-operational-log', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of operational logs
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with the base query
            $logs = VehicleOperationalLog::with(['vehicle', 'assignedUser', 'logger'])
                                        ->orderByDesc('logged_at');

            return Datatables::of($logs)
                // Add the 'vehicle' column from the relationship
                ->addColumn('vehicle', function ($log) {
                    return $log->vehicle->registration_number ?? 'N/A';
                })
                // Add 'log_type' with custom HTML badge
                ->addColumn('log_type', function ($log) {
                    return '<span class="badge bg-' . $log->getLogTypeBadge() . '">' .
                           $log->getLogTypeLabel() . '</span>';
                })
                // Format 'logged_at'
                ->editColumn('logged_at', function ($log) {
                    return $log->logged_at->format('d M Y H:i');
                })
                // Format 'meter_reading'
                ->editColumn('meter_reading', function ($log) { // Assuming meter_reading is a direct column
                    return $log->meter_reading ? number_format($log->meter_reading) . ' km' : 'N/A';
                })
                // Add 'vehicle_status' with custom HTML badge
                ->addColumn('vehicle_status', function ($log) {
                    return $log->vehicle_status ?
                        '<span class="badge bg-' . $log->getStatusBadge() . '">' . ucfirst(str_replace('_', ' ', $log->vehicle_status)) . '</span>' :
                        'N/A';
                })
                // Handle 'assigned_to' conditionally
                ->addColumn('assigned_to', function ($log) {
                    if ($log->log_type === 'assignment') {
                        return $log->assignedUser->name ?? $log->assigned_department ?? 'N/A';
                    }
                    return 'N/A';
                })
                // Add 'logged_by' from the relationship
                ->addColumn('logged_by', function ($log) {
                    return $log->logger->name ?? 'N/A';
                })
                // Add 'actions' column with custom HTML from a partial view
                ->addColumn('actions', function ($log) {
                    // Adjust the view path if different
                    return view('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.partials.actions', compact('log'))->render();
                })
                // Specify which columns contain HTML and should not be escaped
                ->rawColumns(['log_type', 'vehicle_status', 'actions'])
                // Filter and Order for relational and complex columns
                ->filterColumn('vehicle', function($query, $keyword) {
                    $query->whereHas('vehicle', function($q) use ($keyword) {
                        $q->where('registration_number', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('assigned_to', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        // Search by assigned user name if log_type is assignment
                        $q->orWhere(function($sq1) use ($keyword) {
                            $sq1->where('log_type', 'assignment')
                                ->whereHas('assignedUser', function($ssq1) use ($keyword) {
                                    $ssq1->where('name', 'like', "%{$keyword}%");
                                });
                        })
                        // OR search by assigned_department if log_type is assignment
                        ->orWhere(function($sq2) use ($keyword) {
                            $sq2->where('log_type', 'assignment')
                                ->where('assigned_department', 'like', "%{$keyword}%");
                        });
                        // You might also consider matching 'N/A' if the keyword is 'N/A'
                        // ->orWhere(function($sq3) use ($keyword) {
                        //     // This part is more complex as 'N/A' is a display value, not a database one.
                        //     // It would require checking if neither assignedUser nor assigned_department exists
                        //     // for assignment types, or if log_type is not assignment.
                        //     // For simplicity, we'll omit this for now.
                        // });
                    });
                })
                ->filterColumn('logged_by', function($query, $keyword) {
                    $query->whereHas('logger', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                // If 'log_type' or 'vehicle_status' are direct database columns,
                // Yajra will handle their sorting and filtering automatically based on the 'name' property in JS.
                // No need for specific filterColumn/orderColumn unless you want custom logic.
                ->make(true);
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
