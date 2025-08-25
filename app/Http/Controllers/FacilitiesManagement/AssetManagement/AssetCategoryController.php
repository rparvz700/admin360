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
            $query = AssetCategory::query();
            return \Yajra\DataTables\DataTables::of($query)
                ->addColumn('category_name', function ($category) {
                    return $category->category_name;
                })
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
