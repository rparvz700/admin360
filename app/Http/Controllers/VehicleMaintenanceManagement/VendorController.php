<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vendors = Vendor::with('maintenances', 'invoices')
                ->withCount('maintenances')
                ->get()
                ->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'vendor_code' => $vendor->vendor_code,
                        'name' => $vendor->name,
                        'vendor_type' => $vendor->getVendorTypeLabel(),
                        'contact_person' => $vendor->contact_person,
                        'phone' => $vendor->phone,
                        'email' => $vendor->email,
                        'rating' => $vendor->rating ? number_format($vendor->rating, 1) : 'N/A',
                        'maintenances_count' => $vendor->maintenances_count,
                        'total_cost' => number_format($vendor->getTotalMaintenanceCost(), 2),
                        'is_active' => $vendor->is_active,
                        'actions' => view('VehicleManagement.VehicleMaintenance.Vendor.partials.actions', compact('vendor'))->render(),
                    ];
                });

            return response()->json(['data' => $vendors]);
        }

        return view('VehicleManagement.VehicleMaintenance.Vendor.index');
    }

    /**
     * Show the form for creating a new vendor
     */
    public function create()
    {
        return view('VehicleManagement.VehicleMaintenance.Vendor.create');
    }

    /**
     * Store a newly created vendor
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:workshop,parts_supplier,both',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'services_offered' => 'nullable|array',
            'rating' => 'nullable|numeric|min:0|max:5',
            //'is_active' => 'boolean',
        ]);
        
        $validated['vendor_code'] = Vendor::generateVendorCode();
        $validated['is_active'] = $request->has('is_active');
        
        Vendor::create($validated);

        return redirect()->route('VehicleManagement.VehicleMaintenance.Vendor.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified vendor
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['maintenances.vehicle', 'invoices']);
        
        $stats = [
            'total_maintenances' => $vendor->maintenances()->count(),
            'total_cost' => $vendor->getTotalMaintenanceCost(),
            'pending_invoices' => $vendor->invoices()->pending()->count(),
            'overdue_invoices' => $vendor->invoices()->overdue()->count(),
        ];

        return view('VehicleManagement.VehicleMaintenance.Vendor.show', compact('vendor', 'stats'));
    }

    /**
     * Show the form for editing the vendor
     */
    public function edit(Vendor $vendor)
    {
        return view('VehicleManagement.VehicleMaintenance.Vendor.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:workshop,parts_supplier,both',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'services_offered' => 'nullable|array',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $vendor->update($validated);

        return redirect()->route('VehicleManagement.VehicleMaintenance.Vendor.index')
            ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified vendor
     */
    public function destroy(Vendor $vendor)
    {
        if ($vendor->maintenances()->count() > 0) {
            return redirect()->route('VehicleManagement.VehicleMaintenance.Vendor.index')
                ->with('error', 'Cannot delete vendor with existing maintenance records.');
        }

        $vendor->delete();

        return redirect()->route('VehicleManagement.VehicleMaintenance.Vendor.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Show vendor maintenance history
     */
    public function history(Vendor $vendor)
    {
        $maintenances = $vendor->maintenances()
            ->with('vehicle')
            ->orderByDesc('start_datetime')
            ->paginate(20);

        return view('VehicleManagement.VehicleMaintenance.Vendor.history', compact('vendor', 'maintenances'));
    }
}
