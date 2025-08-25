<?php

namespace App\Http\Controllers\FacilitiesManagement\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetAttribute;
use App\Models\AssetCategory;

class AssetAttributeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AssetAttribute::with('category');
            return \Yajra\DataTables\DataTables::of($query)
                ->addColumn('category', function ($attribute) {
                    return $attribute->category ? $attribute->category->category_name : '';
                })
                ->addColumn('attribute_name', function ($attribute) {
                    return $attribute->attribute_name;
                })
                ->addColumn('attribute_type', function ($attribute) {
                    return $attribute->attribute_type;
                })
                ->addColumn('actions', function ($attribute) {
                    return view('FacilitiesManagement.AssetManagement.AssetAttributes.partials.actions', compact('attribute'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('FacilitiesManagement.AssetManagement.AssetAttributes.index');
    }

    public function create()
    {
        $categories = AssetCategory::all();
        return view('FacilitiesManagement.AssetManagement.AssetAttributes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required',
            'options' => 'nullable',
        ]);
        // Convert options to array if present and not empty
        if (!empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode(',', $validated['options']));
        } else {
            $validated['options'] = null;
        }
        AssetAttribute::create($validated);
        return redirect()->route('asset-attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function show($id)
    {
        $attribute = AssetAttribute::with('category')->findOrFail($id);
        return view('FacilitiesManagement.AssetManagement.AssetAttributes.show', compact('attribute'));
    }

    public function edit($id)
    {
        $attribute = AssetAttribute::findOrFail($id);
        $categories = AssetCategory::all();
        return view('FacilitiesManagement.AssetManagement.AssetAttributes.edit', compact('attribute', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required',
            'options' => 'nullable',
        ]);
        // Convert options to array if present and not empty
        if (!empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode(',', $validated['options']));
        } else {
            $validated['options'] = null;
        }
        $attribute = AssetAttribute::findOrFail($id);
        $attribute->update($validated);
        return redirect()->route('asset-attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy($id)
    {
        $attribute = AssetAttribute::findOrFail($id);
        $attribute->delete();
        return redirect()->route('asset-attributes.index')->with('success', 'Attribute deleted successfully.');
    }
}
