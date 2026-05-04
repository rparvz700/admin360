<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\VehiclePart;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VehiclePartController extends Controller
{
    /**
     * Display a listing of vehicle parts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with the base query. Yajra will handle the 'get()' and further processing.
            // withCount('maintenanceParts') is crucial here to include the usage count.
            $parts = VehiclePart::withCount('maintenanceParts');

            return DataTables::of($parts)
                // Direct columns, just use editColumn for formatting if needed, or addColumn if derived
                ->editColumn('part_code', function ($part) {
                    return $part->part_code;
                })
                ->editColumn('part_name', function ($part) {
                    return $part->part_name;
                })
                // Add 'category' with custom HTML badge
                ->addColumn('category', function ($part) {
                    return '<span class="badge bg-' . $part->getCategoryBadge() . '">' .
                           $part->getCategoryLabel() . '</span>';
                })
                // Format 'typical_lifespan_km'
                ->editColumn('typical_lifespan_km', function ($part) {
                    return $part->typical_lifespan_km ? number_format($part->typical_lifespan_km) . ' km' : 'N/A';
                })
                // Format 'typical_lifespan_months'
                ->editColumn('typical_lifespan_months', function ($part) {
                    return $part->typical_lifespan_months ? $part->typical_lifespan_months . ' months' : 'N/A';
                })
                // usage_count comes from withCount('maintenanceParts'), and Yajra automatically picks maintenance_parts_count
                ->editColumn('maintenance_parts_count', function ($part) { // Use the actual attribute name
                    return $part->maintenance_parts_count;
                })
                // Add 'is_active' with custom HTML badge
                ->addColumn('is_active', function ($part) {
                    return $part->is_active ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-secondary">Inactive</span>';
                })
                // Add 'actions' column with custom HTML from a partial view
                ->addColumn('actions', function ($part) {
                    // Adjust the view path if different
                    return view('VehicleManagement.VehicleMaintenance.VehiclePart.partials.actions', compact('part'))->render();
                })
                // Specify which columns contain HTML and should not be escaped
                ->rawColumns(['category', 'is_active', 'actions'])
                // For custom sorting/filtering on 'category' if 'category' isn't a direct DB column or needs custom logic
                // If 'category' is a direct column, Yajra handles it automatically.
                // ->filterColumn('category', function($query, $keyword) {
                //     $query->where('category', 'like', "%{$keyword}%"); // Assuming 'category' is the DB column
                // })
                ->make(true);
        }

        return view('VehicleManagement.VehicleMaintenance.VehiclePart.index');
    }

    /**
     * Show the form for creating a new part
     */
    public function create()
    {
        $categories = [
            'engine' => 'Engine',
            'tyre' => 'Tyre',
            'battery' => 'Battery',
            'oil' => 'Oil / Lubricant',
            'brake' => 'Brake System',
            'body' => 'Body / Cover',
            'transmission' => 'Transmission / Gear',
            'electrical' => 'Electrical',
            'other' => 'Other',
        ];

        return view('VehicleManagement.VehicleMaintenance.VehiclePart.create', compact('categories'));
    }

    /**
     * Store a newly created part
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'category' => 'required|in:engine,tyre,battery,oil,brake,body,transmission,electrical,other',
            'description' => 'nullable|string',
            'typical_lifespan_km' => 'nullable|integer|min:0',
            'typical_lifespan_months' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['part_code'] = VehiclePart::generatePartCode($validated['category']);
        $validated['is_active'] = $request->has('is_active');

        VehiclePart::create($validated);

        return redirect()->route('maintenance.parts.index')
            ->with('success', 'Part created successfully.');
    }

    /**
     * Display the specified part
     */
    public function show(VehiclePart $part)
    {
        $part->load(['maintenanceParts.maintenance.vehicle', 'maintenanceParts.vendor']);
        
        $stats = [
            'total_replacements' => $part->maintenanceParts()->where('action_type', 'replace')->count(),
            'total_repairs' => $part->maintenanceParts()->where('action_type', 'repair')->count(),
            'total_services' => $part->maintenanceParts()->where('action_type', 'service')->count(),
            'total_cost' => $part->maintenanceParts()->sum('part_cost'),
        ];

        return view('VehicleManagement.VehicleMaintenance.VehiclePart.show', compact('part', 'stats'));
    }

    /**
     * Show the form for editing the part
     */
    public function edit(VehiclePart $part)
    {
        $categories = [
            'engine' => 'Engine',
            'tyre' => 'Tyre',
            'battery' => 'Battery',
            'oil' => 'Oil / Lubricant',
            'brake' => 'Brake System',
            'body' => 'Body / Cover',
            'transmission' => 'Transmission / Gear',
            'electrical' => 'Electrical',
            'other' => 'Other',
        ];

        return view('VehicleManagement.VehicleMaintenance.VehiclePart.edit', compact('part', 'categories'));
    }

    /**
     * Update the specified part
     */
    public function update(Request $request, VehiclePart $part)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'category' => 'required|in:engine,tyre,battery,oil,brake,body,transmission,electrical,other',
            'description' => 'nullable|string',
            'typical_lifespan_km' => 'nullable|integer|min:0',
            'typical_lifespan_months' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $part->update($validated);

        return redirect()->route('maintenance.parts.index')
            ->with('success', 'Part updated successfully.');
    }

    /**
     * Remove the specified part
     */
    public function destroy(VehiclePart $part)
    {
        if ($part->maintenanceParts()->count() > 0) {
            return redirect()->route('maintenance.parts.index')
                ->with('error', 'Cannot delete part with existing maintenance history.');
        }

        $part->delete();

        return redirect()->route('maintenance.parts.index')
            ->with('success', 'Part deleted successfully.');
    }

    /**
     * Show part history for specific vehicle
     */
    public function partHistory(VehiclePart $part, Vehicle $vehicle)
    {
        $history = $part->maintenanceParts()
            ->where('vehicle_id', $vehicle->id)
            ->with(['maintenance', 'vendor'])
            ->orderByDesc('created_at')
            ->get();

        return view('VehicleManagement.VehicleMaintenance.VehiclePart.history', compact('part', 'vehicle', 'history'));
    }
}
