<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\VehiclePart;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiclePartController extends Controller
{
    /**
     * Display a listing of vehicle parts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $parts = VehiclePart::withCount('maintenanceParts')
                ->get()
                ->map(function ($part) {
                    return [
                        'id' => $part->id,
                        'part_code' => $part->part_code,
                        'part_name' => $part->part_name,
                        'category' => '<span class="badge bg-' . $part->getCategoryBadge() . '">' . 
                                     $part->getCategoryLabel() . '</span>',
                        'typical_lifespan_km' => $part->typical_lifespan_km ? number_format($part->typical_lifespan_km) . ' km' : 'N/A',
                        'typical_lifespan_months' => $part->typical_lifespan_months ? $part->typical_lifespan_months . ' months' : 'N/A',
                        'usage_count' => $part->maintenance_parts_count,
                        'is_active' => $part->is_active ? 
                            '<span class="badge bg-success">Active</span>' : 
                            '<span class="badge bg-secondary">Inactive</span>',
                        'actions' => view('VehicleManagement.VehicleMaintenance.VehiclePart.partials.actions', compact('part'))->render(),
                    ];
                });

            return response()->json(['data' => $parts]);
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
