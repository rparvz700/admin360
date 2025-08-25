<?php

namespace App\Http\Controllers\FacilitiesManagement\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAttribute;
use App\Models\AssetAttributeValue;

class AssetController extends Controller
{
    // List all assets
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Asset::with(['category', 'floor', 'parent']);
            return \Yajra\DataTables\DataTables::of($query)
                ->addColumn('category', function ($asset) {
                    return $asset->category ? $asset->category->name : '';
                })
                ->addColumn('floor', function ($asset) {
                    return $asset->floor ? $asset->floor->floor_label : '';
                })
                ->addColumn('parent', function ($asset) {
                    return $asset->parent ? ($asset->parent->asset_tag . ' - ' . $asset->parent->asset_name) : '';
                })
                ->addColumn('actions', function ($asset) {
                    return view('FacilitiesManagement.AssetManagement.Assets.partials.actions', compact('asset'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('FacilitiesManagement.AssetManagement.Assets.index');
    }

    // Show create asset form
    public function create()
    {
        $categories = AssetCategory::all();
        $floors = \App\Models\PropertiesFloor::all();
        $assets = Asset::all();
        $attributes = \App\Models\AssetAttribute::all();
        return view('FacilitiesManagement.AssetManagement.Assets.create', compact('categories', 'floors', 'assets', 'attributes'));
    }

    // Store new asset
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:assets',
            'asset_name' => 'required',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable',
            'model' => 'nullable',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'floor_id' => 'nullable|exists:properties_floors,id',
            'location_within_floor' => 'nullable',
            'parent_id' => 'nullable|exists:assets,id',
            'status' => 'required',
        ]);
        $asset = Asset::create($validated);
        // Store attribute values

        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attributeId => $value) {

                if ($value !== null && $value !== '') {
                    \App\Models\AssetAttributeValue::create([
                        'asset_id' => $asset->id,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }
        }
        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    // Show asset details
    public function show($id)
    {
        $asset = Asset::with(['category', 'floor', 'parent', 'children', 'attributeValues.attribute'])->findOrFail($id);
        return view('FacilitiesManagement.AssetManagement.Assets.show', compact('asset'));
    }

    // Show edit asset form
    public function edit($id)
    {
        $asset = Asset::with(['attributeValues'])->findOrFail($id);
        $categories = AssetCategory::all();
        $floors = \App\Models\PropertiesFloor::all();
        $assets = Asset::all();
        $attributes = \App\Models\AssetAttribute::all();
        return view('FacilitiesManagement.AssetManagement.Assets.edit', compact('asset', 'categories', 'floors', 'assets', 'attributes'));
    }

    // Update asset
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:assets,asset_tag,' . $id,
            'asset_name' => 'required',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable',
            'model' => 'nullable',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'floor_id' => 'nullable|exists:properties_floors,id',
            'location_within_floor' => 'nullable',
            'parent_id' => 'nullable|exists:assets,id',
            'status' => 'required',
        ]);
        $asset = Asset::findOrFail($id);
        $asset->update($validated);
        // Update attribute values
        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attributeId => $value) {
                if ($value !== null && $value !== '') {
                    \App\Models\AssetAttributeValue::updateOrCreate(
                        [
                            'asset_id' => $asset->id,
                            'attribute_id' => $attributeId,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                } else {
                    // If value is empty, delete the attribute value if exists
                    \App\Models\AssetAttributeValue::where('asset_id', $asset->id)
                        ->where('attribute_id', $attributeId)
                        ->delete();
                }
            }
        }
        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    // Delete asset
    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }
}
