<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:invoice-management|create-vendor|edit-vendor|delete-vendor', ['only' => ['index', 'show', 'history']]);
        $this->middleware('permission:create-vendor', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vendor', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vendor', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of vendors
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Vendor::with('categories', 'maintenances', 'invoices')
                ->withCount('maintenances');

            if ($request->filled('category_id')) {
                $query->forCategory($request->category_id);
            }

            if ($request->filled('module_scope')) {
                $query->forModule($request->module_scope);
            }

            $vendors = $query->get()->map(function ($vendor) {
                return [
                    'id'                 => $vendor->id,
                    'vendor_code'        => $vendor->vendor_code,
                    'name'               => $vendor->name,
                    'vendor_type'        => $vendor->getVendorTypeLabel(),
                    'category_badges'    => $vendor->getCategoryBadgesHtml(),
                    'contact_person'     => $vendor->contact_person,
                    'phone'              => $vendor->phone,
                    'email'              => $vendor->email,
                    'bank_account_no'    => $vendor->bank_account_no ? ($vendor->bank_name ? $vendor->bank_name . ' (' . $vendor->bank_account_no . ')' : $vendor->bank_account_no) : 'N/A',
                    'tin_vat_no'         => $vendor->tin_vat_no ?? 'N/A',
                    'rating'             => $vendor->rating ? number_format($vendor->rating, 1) : 'N/A',
                    'maintenances_count' => $vendor->maintenances_count,
                    'total_cost'         => number_format($vendor->getTotalMaintenanceCost(), 2),
                    'is_active'          => $vendor->is_active,
                    'actions'            => view('vendors.partials.actions', compact('vendor'))->render(),
                ];
            });

            return response()->json(['data' => $vendors]);
        }

        $categories = VendorCategory::active()->orderBy('module_scope')->orderBy('name')->get();

        return view('vendors.index', compact('categories'));
    }

    /**
     * Show the form for creating a new vendor
     */
    public function create()
    {
        $vendor = new Vendor();
        $categories = VendorCategory::active()->orderBy('module_scope')->orderBy('name')->get();
        return view('vendors.create', compact('vendor', 'categories'));
    }

    /**
     * Store a newly created vendor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'categories'       => 'nullable|array',
            'categories.*'     => 'exists:vendor_categories,id',
            'contact_person'   => 'nullable|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string',
            'bank_name'        => 'nullable|string|max:255',
            'bank_account_no'  => 'nullable|string|max:100',
            'routing_number'   => 'nullable|string|max:100',
            'tin_vat_no'       => 'nullable|string|max:100',
            'services_offered' => 'nullable|array',
            'rating'           => 'nullable|numeric|min:0|max:5',
        ]);

        $validated['vendor_code'] = Vendor::generateVendorCode();
        $validated['is_active']   = $request->has('is_active');

        $vendor = Vendor::create($validated);

        if (!empty($request->categories)) {
            $vendor->categories()->sync($request->categories);
        }

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified vendor
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['categories', 'maintenances.vehicle', 'invoices', 'agreements.building']);

        $stats = [
            'total_maintenances' => $vendor->maintenances()->count(),
            'total_cost'         => $vendor->getTotalMaintenanceCost(),
            'total_agreements'   => $vendor->agreements()->count(),
            'pending_invoices'   => $vendor->invoices()->pending()->count(),
            'overdue_invoices'   => $vendor->invoices()->overdue()->count(),
        ];

        return view('vendors.show', compact('vendor', 'stats'));
    }

    /**
     * Show the form for editing the vendor
     */
    public function edit(Vendor $vendor)
    {
        $vendor->load('categories');
        $categories = VendorCategory::active()->orderBy('module_scope')->orderBy('name')->get();
        $assignedCategoryIds = $vendor->categories->pluck('id')->toArray();

        return view('vendors.edit', compact('vendor', 'categories', 'assignedCategoryIds'));
    }

    /**
     * Update the specified vendor
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'categories'       => 'nullable|array',
            'categories.*'     => 'exists:vendor_categories,id',
            'contact_person'   => 'nullable|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string',
            'bank_name'        => 'nullable|string|max:255',
            'bank_account_no'  => 'nullable|string|max:100',
            'routing_number'   => 'nullable|string|max:100',
            'tin_vat_no'       => 'nullable|string|max:100',
            'services_offered' => 'nullable|array',
            'rating'           => 'nullable|numeric|min:0|max:5',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $vendor->update($validated);

        if ($request->has('categories')) {
            $vendor->categories()->sync($request->categories ?? []);
        }

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified vendor
     */
    public function destroy(Vendor $vendor)
    {
        if ($vendor->maintenances()->count() > 0 || $vendor->agreements()->count() > 0) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with existing maintenance or agreement records.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')
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

        return view('vendors.history', compact('vendor', 'maintenances'));
    }
}
