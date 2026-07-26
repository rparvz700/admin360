<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\VatTax;
use Illuminate\Http\Request;

class VatTaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:invoice-management|create-vat-tax|edit-vat-tax|delete-vat-tax', ['only' => ['index', 'show', 'list']]);
        $this->middleware('permission:create-vat-tax', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vat-tax', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vat-tax', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('InvoiceManagement.VatTaxes.index');
    }

    public function list(Request $request)
    {
        $query = VatTax::query();
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('vat', 'like', "%{$search}%")
                    ->orWhere('tax', 'like', "%{$search}%");
            });
        }

        $total = VatTax::query()->count();
        $filtered = $query->count();
        $vatTaxes = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();

        $data = [];
        foreach ($vatTaxes as $vatTax) {
            $data[] = [
                'id' => $vatTax->id,
                'type' => $vatTax->type,
                'vat' => $vatTax->vat !== null ? number_format((float) $vatTax->vat, 2) : '-',
                'tax' => $vatTax->tax !== null ? number_format((float) $vatTax->tax, 2) : '-',
                'status' => $vatTax->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>',
                'actions' => view('InvoiceManagement.VatTaxes.partials.actions', compact('vatTax'))->render(),
            ];
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function create()
    {
        return view('InvoiceManagement.VatTaxes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'vat' => 'nullable|numeric|min:0|max:999999.99',
            'tax' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|boolean',
        ]);

        VatTax::create($validated);

        return redirect()->route('vat-taxes.index')->with('success', 'VAT/TAX configuration created successfully.');
    }

    public function show(VatTax $vatTax)
    {
        return view('InvoiceManagement.VatTaxes.show', compact('vatTax'));
    }

    public function edit(VatTax $vatTax)
    {
        return view('InvoiceManagement.VatTaxes.edit', compact('vatTax'));
    }

    public function update(Request $request, VatTax $vatTax)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'vat' => 'nullable|numeric|min:0|max:999999.99',
            'tax' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|boolean',
        ]);

        $vatTax->update($validated);

        return redirect()->route('vat-taxes.index')->with('success', 'VAT/TAX configuration updated successfully.');
    }

    public function destroy(VatTax $vatTax)
    {
        $vatTax->delete();

        return redirect()->route('vat-taxes.index')->with('success', 'VAT/TAX configuration deleted successfully.');
    }
}
