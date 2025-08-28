<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleDocumentAttribute;
use App\Models\VehicleDocumentCategory;

class VehicleDocumentAttributeController extends Controller
{
    public function index()
    {
        return view('VehicleManagement.VehicleDocumentAttributes.index');
    }

    public function list(Request $request)
    {
        $query = VehicleDocumentAttribute::with('category');
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('attribute_name', 'like', "%$search%")
                  ->orWhere('attribute_type', 'like', "%$search%")
                ;
            });
        }
        $total = VehicleDocumentAttribute::count();
        $filtered = $query->count();
        $attributes = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($attributes as $attribute) {
            $data[] = [
                'id' => $attribute->id,
                'category' => $attribute->category->category_name ?? '',
                'attribute_name' => $attribute->attribute_name,
                'attribute_type' => $attribute->attribute_type,
                'options' => $attribute->options,
                'actions' => view('VehicleManagement.VehicleDocumentAttributes.partials.actions', compact('attribute'))->render(),
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
        $categories = VehicleDocumentCategory::all();
        return view('VehicleManagement.VehicleDocumentAttributes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:vehicle_document_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required|in:string,number,date,boolean,select',
            'options' => 'nullable',
        ]);
        VehicleDocumentAttribute::create($validated);
        return redirect()->route('vehicle-document-attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function show($id)
    {
        $attribute = VehicleDocumentAttribute::with('category')->findOrFail($id);
        return view('VehicleManagement.VehicleDocumentAttributes.show', compact('attribute'));
    }

    public function edit($id)
    {
        $attribute = VehicleDocumentAttribute::findOrFail($id);
        $categories = VehicleDocumentCategory::all();
        return view('VehicleManagement.VehicleDocumentAttributes.edit', compact('attribute', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $attribute = VehicleDocumentAttribute::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:vehicle_document_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required|in:string,number,date,boolean,select',
            'options' => 'nullable',
        ]);
        $attribute->update($validated);
        return redirect()->route('vehicle-document-attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy($id)
    {
        $attribute = VehicleDocumentAttribute::findOrFail($id);
        $attribute->delete();
        return redirect()->route('vehicle-document-attributes.index')->with('success', 'Attribute deleted successfully.');
    }
}
