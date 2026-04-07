<?php

namespace App\Http\Controllers\FacilitiesManagement\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;

class AssetCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Select the columns explicitly for better performance
            $query = AssetCategory::select(['id', 'category_name', 'description']);

            return \Yajra\DataTables\DataTables::of($query)
                // REMOVED addColumn for category_name. 
                // Yajra will now see it as a real DB column and search it automatically.
                ->addColumn('actions', function ($category) {
                    return view('FacilitiesManagement.AssetManagement.AssetCategories.partials.actions', compact('category'))->render();
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('FacilitiesManagement.AssetManagement.AssetCategories.index');
    }

    public function create()
    {
        return view('FacilitiesManagement.AssetManagement.AssetCategories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|unique:asset_categories,category_name',
            'description' => 'nullable',
        ]);
        AssetCategory::create($validated);
        return redirect()->route('asset-categories.index')->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $category = AssetCategory::findOrFail($id);
        return view('FacilitiesManagement.AssetManagement.AssetCategories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = AssetCategory::findOrFail($id);
        return view('FacilitiesManagement.AssetManagement.AssetCategories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_name' => 'required|unique:asset_categories,category_name,' . $id,
            'description' => 'nullable',
        ]);
        $category = AssetCategory::findOrFail($id);
        $category->update($validated);
        return redirect()->route('asset-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = AssetCategory::findOrFail($id);
        $category->delete();
        return redirect()->route('asset-categories.index')->with('success', 'Category deleted successfully.');
    }
}
