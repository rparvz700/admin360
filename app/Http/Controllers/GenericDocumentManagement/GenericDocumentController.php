<?php

namespace App\Http\Controllers\GenericDocumentManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GenericDocument;
use App\Models\GenericDocumentCategory;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class GenericDocumentController extends Controller
{
    public function index()
    {
        return view('GenericDocumentManagement.GenericDocument.index');
    }

    public function list(Request $request)
    {
        $query = GenericDocument::with(['documentable', 'category']);

        // DataTables variables
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');

        // 🔍 Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                ->orWhere('documentable_type', 'like', "%$search%")
                ->orWhere('issue_date', 'like', "%$search%")
                ->orWhere('expiry_date', 'like', "%$search%")
                ->orWhereHasMorph(
                    'documentable',
                    config('app.documentableTypes'),
                    function ($q2) use ($search) {
                        // Search common name-like columns
                        $q2->where('name', 'like', "%$search%")
                            ->orWhere('registration_number', 'like', "%$search%")
                            ->orWhere('title', 'like', "%$search%");
                    }
                )
                ->orWhereHas('category', function ($q2) use ($search) {
                    $q2->where('category_name', 'like', "%$search%");
                });
            });
        }

        // Counts
        $total = GenericDocument::count();
        $filtered = $query->count();

        // Pagination
        $documents = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $documentableTypes = config('app.documentableTypes');

        $documents->transform(function ($doc) use ($documentableTypes) {
            $doc->primary_text = null;

            foreach ($documentableTypes as $type => $config) {
                // Make sure $config is array
                if (is_array($config) && ($config['class'] ?? null) === $doc->documentable_type) {
                    $field = $config['display_field'] ?? 'name';
                    $doc->primary_text = $doc->documentable->{$field} ?? '';
                    break;
                }
            }

            return $doc;
        });


        // Data formatting
        $data = $documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'documentable_type' => class_basename($doc->documentable_type),
                'primary_text' => $doc->primary_text, // <-- comes from your accessor
                'category' => $doc->category->category_name ?? '',
                'issue_date' => $doc->issue_date,
                'expiry_date' => $doc->expiry_date,
                'actions' => view('GenericDocumentManagement.GenericDocument.partials.actions', compact('doc'))->render(),
            ];
        });

        // Return DataTables-compatible JSON
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }



    public function create()
    {
        $documentableTypes = collect(config('app.documentableTypes'))
                                ->map(function($config) { return $config['class']; })
                                ->toArray();
        //$vehicles = \App\Models\Vehicle::all();
        $categories = GenericDocumentCategory::all();
        $attributes = \App\Models\GenericDocumentAttribute::all()->map(function($attr) {
            $arr = $attr->toArray();
            // Parse options as array if it's a JSON string or comma-separated
            if (is_string($arr['options'])) {
                $decoded = json_decode($arr['options'], true);
                $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
            }
            return $arr;
        });
        $oldAttributeValues = old('attributes', []);
        return view('GenericDocumentManagement.GenericDocument.create', compact('documentableTypes', 'categories', 'attributes', 'oldAttributeValues'));
        //return view('GenericDocumentManagement.GenericDocument.create', compact('vehicles', 'categories', 'attributes', 'oldAttributeValues'));
    }


    public function fetchDocumentables(Request $request)
    {
        $typeKey = strtolower($request->get('type')); // e.g. 'vehicle', 'asset', etc.
        $documentableTypes = config('app.documentableTypes');
        // Validate type
        if (!isset($documentableTypes[$typeKey])) {
            return response()->json([]);
        }
        
        $class = $documentableTypes[$typeKey]['class'] ?? null;
        $displayColumn = $documentableTypes[$typeKey]['display_field'] ?? 'id';
        // Validate class exists
        if (!class_exists($class)) {
            return response()->json([]);
        }

        // Fetch documents: id => label
        return $class::select('id', $displayColumn.' AS label')->pluck('label', 'id');
    }




    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'vehicle_id' => 'required|exists:vehicles,id',
    //         'category_id' => 'required|exists:vehicle_document_categories,id',
    //         'issue_date' => 'required|date',
    //         'expiry_date' => 'nullable|date',
    //     ]);
    //     $doc = GenericDocument::create($validated);

    //     // Store attribute values
    //     $attributes = $request->input('attributes', []);
    //     if (!empty($attributes)) {
    //         foreach ($attributes as $attribute_id => $value) {
    //             \App\Models\GenericDocumentAttributeValue::create([
    //                 'document_id' => $doc->id,
    //                 'attribute_id' => $attribute_id,
    //                 'value' => is_array($value) ? json_encode($value) : $value,
    //             ]);
    //         }
    //     }

    //     return redirect()->route('vehicle-documents.index')->with('success', 'Vehicle document created successfully.');
    // }

    public function store(Request $request)
    {
        $documentableTypes = config('app.documentableTypes');
        $selectedValue = $request->input('documentable_type'); // 'asset'
        $selectedLabel = ucfirst($selectedValue);  
        $validated = $request->validate([
            'documentable_type' => 'required|string', // e.g. 'vehicle', 'asset'
            'documentable_id'   => 'required|integer',
            'category_id'       => 'required|exists:generic_document_categories,id',
            'issue_date'        => 'required|date',
            'expiry_date'       => 'nullable|date',
        ]);

        $docTypeKey = $validated['documentable_type'];
        //dd($validated, $docTypeKey,$selectedValue,$selectedLabel, $documentableTypes);
        if (!isset($documentableTypes[$docTypeKey])) {
            return back()->withErrors(['documentable_type' => 'Invalid type selected.']);
        }

        // Map to fully qualified class
        $validated['documentable_type'] = $documentableTypes[$docTypeKey]['class'];
        // dd($validated);
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents', 'public');
            $validated['file_path'] = $path;
        }

        $doc = GenericDocument::create($validated);

        // Store attribute values
        $attributes = $request->input('attributes', []);
        foreach ($attributes as $attribute_id => $value) {
            \App\Models\GenericDocumentAttributeValue::create([
                'document_id'  => $doc->id,
                'attribute_id' => $attribute_id,
                'value'        => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        return redirect()->route('generic-documents.index')
                        ->with('success', 'Document created successfully.');
    }



    public function show($id)
    {
        $documentableTypes = config('app.documentableTypes');
        $doc = GenericDocument::with(['documentable', 'category'])->findOrFail($id);

        $primaryText = null;
        $typeLabel = class_basename($doc->documentable_type); // Default fallback, e.g., "Vehicle"

        foreach ($documentableTypes as $type => $config) {
            if (is_array($config) && ($config['class'] ?? null) === $doc->documentable_type) {
                $field = $config['display_field'] ?? 'id';
                $primaryText = $doc->documentable->{$field} ?? '';
                $typeLabel = ucfirst($type); // "vehicle" → "Vehicle"
                break;
            }
        }
        //dd($doc, $primaryText, $typeLabel);
        return view('GenericDocumentManagement.GenericDocument.show', compact('doc', 'primaryText', 'typeLabel'));
    }



    // public function edit($id)
    // {
    //     $doc = GenericDocument::findOrFail($id);
    //     $vehicles = Vehicle::all();
    //     $categories = GenericDocumentCategory::all();
    //     $attributes = \App\Models\GenericDocumentAttribute::all()->map(function($attr) {
    //         $arr = $attr->toArray();
    //         if (is_string($arr['options'])) {
    //             $decoded = json_decode($arr['options'], true);
    //             $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
    //         }
    //         return $arr;
    //     });
    //     // Prepare old values for attribute fields
    //     $oldAttributeValues = old('attributes');
    //     if (!$oldAttributeValues) {
    //         $oldAttributeValues = $doc->attributeValues->pluck('value', 'attribute_id')->toArray();
    //     }
    //     return view('GenericDocumentManagement.GenericDocument.edit', compact('doc', 'vehicles', 'categories', 'attributes', 'oldAttributeValues'));
    // }

    // public function edit($id)
    // {
    //     $documentableTypes = config('app.documentableTypes');

    //     $doc = GenericDocument::with(['documentable', 'category', 'attributeValues'])->findOrFail($id);
    //     $categories = GenericDocumentCategory::all();

    //     // --- Prepare documentable dropdowns ---
    //     // The user can select from: Vehicle, Asset, Driver, Agreement, etc.
    //     $documentableTypes = collect($documentableTypes)->map(function ($config, $key) {
    //         return [
    //             'key' => $key, // e.g. 'vehicle'
    //             'label' => ucfirst($key), // e.g. 'Vehicle'
    //             'class' => $config['class'],
    //         ];
    //     });

    //     // Current documentable info (for preselecting)
    //     $currentType = collect($documentableTypes)->firstWhere('class', $doc->documentable_type);
    //     $selectedType = array_search($currentType, $documentableTypes->toArray(), true) ?: array_key_first($documentableTypes);

    //     // --- Dynamic documentable items for current type ---
    //     $currentTypeKey = array_search($doc->documentable_type, array_column($documentableTypes->toArray(), 'class'));
    //     $currentConfig = collect($documentableTypes)->firstWhere('class', $doc->documentable_type);
    //     $displayColumn = $currentConfig['display_field'] ?? 'id';
    //     $class = $currentConfig['class'] ?? null;

    //     $documentables = [];
    //     if ($class && class_exists($class)) {
    //         $documentables = $class::select('id', $displayColumn . ' AS label')->get();
    //     }

    //     // --- Attributes handling ---
    //     $attributes = \App\Models\GenericDocumentAttribute::all()->map(function ($attr) {
    //         $arr = $attr->toArray();
    //         if (is_string($arr['options'])) {
    //             $decoded = json_decode($arr['options'], true);
    //             $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
    //         }
    //         return $arr;
    //     });

    //     // --- Attribute values (old or from DB) ---
    //     $oldAttributeValues = old('attributes') ?: $doc->attributeValues->pluck('value', 'attribute_id')->toArray();
    //     //dd($doc, $documentableOptions, $documentables, $selectedType, $attributes, $oldAttributeValues);
    //     return view('GenericDocumentManagement.GenericDocument.edit', compact(
    //         'doc',
    //         'categories',
    //         'documentableTypes',
    //         'documentables',
    //         'attributes',
    //         'oldAttributeValues',
    //         'selectedType'
    //     ));
    // }

    public function edit($id)
    {
        $documentableTypes = config('app.documentableTypes');
        $doc = GenericDocument::with('documentable')->findOrFail($id);
        $categories = GenericDocumentCategory::all();

        $attributes = \App\Models\GenericDocumentAttribute::all()->map(function($attr) {
            $arr = $attr->toArray();
            if (is_string($arr['options'])) {
                $decoded = json_decode($arr['options'], true);
                $arr['options'] = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $arr['options']);
            }
            return $arr;
        });

        // Prepare old values for custom attribute fields
        $oldAttributeValues = old('attributes');
        if (!$oldAttributeValues) {
            $oldAttributeValues = $doc->attributeValues->pluck('value', 'attribute_id')->toArray();
        }

        // Find the documentable label and the available options for the dropdown
        $selectedDocumentableType = null;
        $documentables = collect();

        foreach ($documentableTypes as $label => $config) {
            if ($config['class'] === $doc->documentable_type) {
                $selectedDocumentableType = $label;
                $documentables = $config['class']::select('id', $config['display_field'] . ' AS label')->get();
                break;
            }
        }

        return view('GenericDocumentManagement.GenericDocument.edit', compact(
            'doc',
            'categories',
            'attributes',
            'oldAttributeValues',
            'documentableTypes',
            'documentables',
            'selectedDocumentableType'
        ));
    }

    public function update(Request $request, $id)
    {
        $doc = GenericDocument::findOrFail($id);

        $validated = $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id'   => 'required|integer',
            'category_id'       => 'required|exists:generic_document_categories,id',
            'issue_date'        => 'required|date',
            'expiry_date'       => 'nullable|date',
        ]);
        
        $documentableTypes = config('app.documentableTypes');

        // Replace label (e.g. "asset") with actual class name (e.g. "App\Models\Asset")
        if (isset($validated['documentable_type'], $documentableTypes[$validated['documentable_type']])) {
            $validated['documentable_type'] = $documentableTypes[$validated['documentable_type']]['class'];
        }

        // Detect if category or documentable type changed
        $categoryChanged = $doc->category_id != $validated['category_id'];
        $documentableTypeChanged = $doc->documentable_type != $validated['documentable_type']
            || $doc->documentable_id != $validated['documentable_id'];
        //dd($validated   , $categoryChanged, $documentableTypeChanged);
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $path = $request->file('file')->store('documents', 'public');
            $validated['file_path'] = $path;
        }

        // Update main document fields
        $doc->update($validated);

        // If category or documentable type changed → delete old attribute values
        if ($categoryChanged || $documentableTypeChanged) {
            \App\Models\GenericDocumentAttributeValue::where('document_id', $doc->id)->delete();
        }

        // Save new attribute values
        $attributes = $request->input('attributes', []);
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_id => $value) {
                \App\Models\GenericDocumentAttributeValue::updateOrCreate(
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

        return redirect()
            ->route('generic-documents.index')
            ->with('success', 'Generic document updated successfully.');
    }


    public function destroy($id)
    {
        $doc = GenericDocument::findOrFail($id);
        $doc->delete();
        return redirect()->route('generic-documents.index')->with('success', 'Generic document deleted successfully.');
    }
}
