<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenance;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Invoice;
use App\Models\VehiclePart;
use App\Models\VehicleMaintenancePart;
use App\Models\VehicleOperationalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class VehicleMaintenanceController extends Controller
{
    /**
     * Display a listing of maintenance records
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with the base query. Yajra will handle the 'get()' and further processing.
            $maintenances = VehicleMaintenance::with(['vehicle', 'vendor', 'invoice'])
                                ->orderByDesc('start_datetime'); // Apply default ordering if desired

            return DataTables::of($maintenances)
                // Add the 'vehicle' column, transforming it from the relationship
                ->addColumn('vehicle', function ($maintenance) {
                    return $maintenance->vehicle->registration_number ?? 'N/A';
                })
                // Add 'maintenance_type' with custom HTML
                ->addColumn('maintenance_type', function ($maintenance) {
                    return '<span class="badge bg-' . $maintenance->getMaintenanceTypeBadge() . '">' .
                           $maintenance->getMaintenanceTypeLabel() . '</span>';
                })
                // Format 'start_datetime'
                ->editColumn('start_datetime', function ($maintenance) {
                    return $maintenance->start_datetime->format('d M Y H:i');
                })
                // Add the 'vendor' column, transforming it from the relationship
                ->addColumn('vendor', function ($maintenance) {
                    return $maintenance->vendor->name ?? 'N/A';
                })
                // Format 'labor_cost'
                ->addColumn('labor_cost', function ($maintenance) {
                    return '৳ ' . number_format($maintenance->labor_cost, 2);
                })
                // Format 'parts_cost'
                ->addColumn('parts_cost', function ($maintenance) {
                    return '৳ ' . number_format($maintenance->parts_cost, 2);
                })
                // Add 'total_cost' (assuming total_service_cost is an accessor or calculated property)
                ->addColumn('total_cost', function ($maintenance) {
                    return '৳ ' . number_format($maintenance->total_service_cost, 2);
                })
                // Add 'status' with custom HTML
                ->addColumn('status', function ($maintenance) {
                    return '<span class="badge bg-secondary">' . ucfirst($maintenance->status) . '</span>';
                })
                // Add 'actions' column with custom HTML from a partial view
                ->addColumn('actions', function ($maintenance) {
                    return view('VehicleManagement.VehicleMaintenance.partials.actions', compact('maintenance'))->render();
                })
                // Specify which columns contain HTML and should not be escaped
                ->rawColumns(['maintenance_type', 'status', 'actions'])
                // For relational columns, if you want them to be searchable/sortable, you might need to use `filterColumn` and `orderColumn`.
                // Example for vehicle search:
                ->filterColumn('vehicle', function($query, $keyword) {
                    $query->whereHas('vehicle', function($q) use ($keyword) {
                        $q->where('registration_number', 'like', "%{$keyword}%");
                    });
                })
                // Example for vendor search:
                ->filterColumn('vendor', function($query, $keyword) {
                    $query->whereHas('vendor', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->make(true);
        }

        return view('VehicleManagement.VehicleMaintenance.index');
    }

    /**
     * Show the form for creating a new maintenance record
     */
    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->get();
        $vendors = Vendor::active()->get();
        $parts = VehiclePart::active()->orderBy('part_name')->get();

        return view('VehicleManagement.VehicleMaintenance.create', compact('vehicles', 'vendors', 'parts'));
    }

    /**
     * Store a newly created maintenance record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'maintenance_type' => 'required|in:routine,breakdown,accident,inspection',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'start_datetime' => 'required|date',
            'estimated_end_datetime' => 'nullable|date|after:start_datetime',
            'actual_end_datetime' => 'nullable|date|after:start_datetime',
            'service_description' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'meter_reading_at_service' => 'nullable|integer|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'next_service_due_date' => 'nullable|date',
            'next_service_due_km' => 'nullable|integer|min:0',
            'performed_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            
            // Parts data
            'parts' => 'nullable|array',
            'parts.*.vehicle_part_id' => 'required|exists:vehicle_parts,id',
            'parts.*.action_type' => 'required|in:replace,repair,service',
            'parts.*.quantity' => 'required|integer|min:1',
            'parts.*.part_cost' => 'required|numeric|min:0',
            'parts.*.tyre_position' => 'nullable|string',
            'parts.*.warranty_period_months' => 'nullable|integer|min:0',
            'parts.*.next_replacement_due_date' => 'nullable|date',
            'parts.*.next_replacement_due_km' => 'nullable|integer|min:0',
            'parts.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate costs
            $parts_cost = 0;
            if ($request->has('parts')) {
                foreach ($request->parts as $part) {
                    $parts_cost += $part['part_cost'] * $part['quantity'];
                }
            }

            $validated['parts_cost'] = $parts_cost;
            $validated['total_service_cost'] = ($validated['labor_cost'] ?? 0) + $parts_cost;
            $validated['current_service_completed'] = $validated['status'] === 'completed';

            $maintenance = VehicleMaintenance::create($validated);

            // Store parts if provided
            if ($request->has('parts')) {
                foreach ($request->parts as $partData) {
                    $partData['vehicle_maintenance_id'] = $maintenance->id;
                    $partData['vehicle_id'] = $validated['vehicle_id'];
                    $partData['vendor_id'] = $validated['vendor_id'];
                    
                    if (isset($partData['warranty_period_months']) && $partData['warranty_period_months'] > 0) {
                        $partData['warranty_expiry_date'] = now()->addMonths($partData['warranty_period_months']);
                    }

                    VehicleMaintenancePart::create($partData);
                }
            }

            // Update meter reading if provided
            if ($request->filled('meter_reading_at_service')) {
                VehicleOperationalLog::create([
                    'vehicle_id' => $validated['vehicle_id'],
                    'log_type' => 'meter_reading',
                    'meter_reading' => $request->meter_reading_at_service,
                    'remarks' => 'Updated during maintenance',
                    'logged_by' => Auth::id(),
                    'logged_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('maintenance.maintenances.index')
                ->with('success', 'Maintenance record created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating maintenance record: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified maintenance record
     */
    public function show(VehicleMaintenance $maintenance)
    {
        $maintenance->load(['vehicle', 'vendor', 'invoice', 'maintenanceParts.part', 'approver']);

        return view('VehicleManagement.VehicleMaintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the maintenance record
     */
    public function edit(VehicleMaintenance $maintenance)
    {
        $vehicles = Vehicle::where('status', 'active')->get();
        $vendors = Vendor::active()->get();
        $parts = VehiclePart::active()->get();
        $maintenance->load('maintenanceParts');

        return view('VehicleManagement.VehicleMaintenance.edit', compact('maintenance', 'vehicles', 'vendors', 'parts'));
    }

    /**
     * Update the specified maintenance record
     */
    public function update(Request $request, VehicleMaintenance $maintenance)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'maintenance_type' => 'required|in:routine,breakdown,accident,inspection',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'start_datetime' => 'required|date',
            'estimated_end_datetime' => 'nullable|date|after:start_datetime',
            'actual_end_datetime' => 'nullable|date|after:start_datetime',
            'service_description' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'meter_reading_at_service' => 'nullable|integer|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'next_service_due_date' => 'nullable|date',
            'next_service_due_km' => 'nullable|integer|min:0',
            'performed_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            
            // Parts data
            'parts' => 'nullable|array',
            'parts.*.vehicle_part_id' => 'required|exists:vehicle_parts,id',
            'parts.*.action_type' => 'required|in:replace,repair,service',
            'parts.*.quantity' => 'required|integer|min:1',
            'parts.*.part_cost' => 'required|numeric|min:0',
            'parts.*.tyre_position' => 'nullable|string',
            'parts.*.warranty_period_months' => 'nullable|integer|min:0',
            'parts.*.next_replacement_due_date' => 'nullable|date',
            'parts.*.next_replacement_due_km' => 'nullable|integer|min:0',
            'parts.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate costs
            $parts_cost = 0;
            if ($request->has('parts')) {
                foreach ($request->parts as $part) {
                    $parts_cost += $part['part_cost'] * $part['quantity'];
                }
            }

            $validated['parts_cost'] = $parts_cost;
            $validated['total_service_cost'] = ($validated['labor_cost'] ?? 0) + $parts_cost;
            $validated['current_service_completed'] = $validated['status'] === 'completed';

            $maintenance->update($validated);

            // Update parts - delete old and create new
            $maintenance->maintenanceParts()->delete();
            
            if ($request->has('parts')) {
                foreach ($request->parts as $partData) {
                    $partData['vehicle_maintenance_id'] = $maintenance->id;
                    $partData['vehicle_id'] = $validated['vehicle_id'];
                    $partData['vendor_id'] = $validated['vendor_id'];
                    
                    if (isset($partData['warranty_period_months']) && $partData['warranty_period_months'] > 0) {
                        $partData['warranty_expiry_date'] = now()->addMonths($partData['warranty_period_months']);
                    }

                    VehicleMaintenancePart::create($partData);
                }
            }

            DB::commit();

            return redirect()->route('maintenance.maintenances.index')
                ->with('success', 'Maintenance record updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating maintenance record: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified maintenance record
     */
    public function destroy(VehicleMaintenance $maintenance)
    {
        DB::beginTransaction();
        try {
            $maintenance->maintenanceParts()->delete();
            $maintenance->delete();
            
            DB::commit();

            return redirect()->route('maintenance.maintenances.index')
                ->with('success', 'Maintenance record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting maintenance record: ' . $e->getMessage());
        }
    }

    /**
     * Approve maintenance record
     */
    public function approve(VehicleMaintenance $maintenance)
    {
        $maintenance->update([
            'approved_by' => Auth::id(),
            'status' => 'completed',
            'current_service_completed' => true,
        ]);

        return redirect()->route('maintenance.maintenances.show', $maintenance)
            ->with('success', 'Maintenance record approved successfully.');
    }
}
