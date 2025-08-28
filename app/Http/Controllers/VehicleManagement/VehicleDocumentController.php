<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleDocument;
use App\Models\Vehicle;
use App\Models\VehicleDocumentCategory;

class VehicleDocumentController extends Controller
{
    public function index()
    {
        return view('VehicleManagement.VehicleDocument.index');
    }

    public function list(Request $request)
    {
        $query = VehicleDocument::with(['vehicle', 'category']);
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('document_number', 'like', "%$search%")
                  ->orWhere('issue_date', 'like', "%$search%")
                  ->orWhere('expiry_date', 'like', "%$search%")
                  ->orWhereHas('vehicle', function($q2) use ($search) {
                      $q2->where('registration_number', 'like', "%$search%")
                         ->orWhere('vehicle_name', 'like', "%$search%")
                         ;
                  })
                  ->orWhereHas('category', function($q2) use ($search) {
                      $q2->where('category_name', 'like', "%$search%")
                         ;
                  });
            });
        }
        $total = VehicleDocument::count();
        $filtered = $query->count();
        $documents = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($documents as $doc) {
            $data[] = [
                'id' => $doc->id,
                'vehicle' => $doc->vehicle->registration_number ?? '',
                'category' => $doc->category->category_name ?? '',
                'issue_date' => $doc->issue_date,
                'expiry_date' => $doc->expiry_date,
                'actions' => view('VehicleManagement.VehicleDocument.partials.actions', compact('doc'))->render(),
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
        $vehicles = Vehicle::all();
        $categories = VehicleDocumentCategory::all();
        $attributes = \App\Models\VehicleDocumentAttribute::all()->map(function($attr) {
            $arr = $attr->toArray();
            // Parse options as array if it's a JSON string or comma-separated
            if (is_string($arr['options'])) {
                $decoded = json_decode($arr['options'], true);
                $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
            }
            return $arr;
        });
        $oldAttributeValues = old('attributes', []);
        return view('VehicleManagement.VehicleDocument.create', compact('vehicles', 'categories', 'attributes', 'oldAttributeValues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'category_id' => 'required|exists:vehicle_document_categories,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date',
        ]);
        $doc = VehicleDocument::create($validated);

        // Store attribute values
        $attributes = $request->input('attributes', []);
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_id => $value) {
                \App\Models\VehicleDocumentAttributeValue::create([
                    'document_id' => $doc->id,
                    'attribute_id' => $attribute_id,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }

        return redirect()->route('vehicle-documents.index')->with('success', 'Vehicle document created successfully.');
    }

    public function show($id)
    {
        $doc = VehicleDocument::with(['vehicle', 'category'])->findOrFail($id);
        return view('VehicleManagement.VehicleDocument.show', compact('doc'));
    }

    public function edit($id)
    {
        $doc = VehicleDocument::findOrFail($id);
        $vehicles = Vehicle::all();
        $categories = VehicleDocumentCategory::all();
        $attributes = \App\Models\VehicleDocumentAttribute::all()->map(function($attr) {
            $arr = $attr->toArray();
            if (is_string($arr['options'])) {
                $decoded = json_decode($arr['options'], true);
                $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
            }
            return $arr;
        });
        // Prepare old values for attribute fields
        $oldAttributeValues = old('attributes');
        if (!$oldAttributeValues) {
            $oldAttributeValues = $doc->attributeValues->pluck('value', 'attribute_id')->toArray();
        }
        return view('VehicleManagement.VehicleDocument.edit', compact('doc', 'vehicles', 'categories', 'attributes', 'oldAttributeValues'));
    }

    public function update(Request $request, $id)
    {
        $doc = VehicleDocument::findOrFail($id);
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'category_id' => 'required|exists:vehicle_document_categories,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date',
        ]);
        $doc->update($validated);

        // Update attribute values
        $attributes = $request->input('attributes', []);
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_id => $value) {
                \App\Models\VehicleDocumentAttributeValue::updateOrCreate(
                    [
                        'document_id' => $doc->id,
                        'attribute_id' => $attribute_id,
                    ],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ]
                );
            }
        }

        return redirect()->route('vehicle-documents.index')->with('success', 'Vehicle document updated successfully.');
    }

    public function destroy($id)
    {
        $doc = VehicleDocument::findOrFail($id);
        $doc->delete();
        return redirect()->route('vehicle-documents.index')->with('success', 'Vehicle document deleted successfully.');
    }
}
