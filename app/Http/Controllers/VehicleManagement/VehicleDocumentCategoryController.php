<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleDocumentCategory;

class VehicleDocumentCategoryController extends Controller
{
    public function index()
    {
        return view('VehicleManagement.VehicleDocumentCategories.index');
    }

    public function list(Request $request)
    {
        $query = VehicleDocumentCategory::query();
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('category_name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                ;
            });
        }
        $total = VehicleDocumentCategory::query()->count();
        $filtered = $query->count();
        $categories = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'actions' => view('VehicleManagement.VehicleDocumentCategories.partials.actions', compact('category'))->render(),
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
        return view('VehicleManagement.VehicleDocumentCategories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|unique:vehicle_document_categories,category_name',
            'description' => 'nullable',
        ]);
        VehicleDocumentCategory::create($validated);
        return redirect()->route('vehicle-document-categories.index')->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $category = VehicleDocumentCategory::findOrFail($id);
        return view('VehicleManagement.VehicleDocumentCategories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = VehicleDocumentCategory::findOrFail($id);
        return view('VehicleManagement.VehicleDocumentCategories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = VehicleDocumentCategory::findOrFail($id);
        $validated = $request->validate([
            'category_name' => 'required|unique:vehicle_document_categories,category_name,' . $id,
            'description' => 'nullable',
        ]);
        $category->update($validated);
        return redirect()->route('vehicle-document-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = VehicleDocumentCategory::findOrFail($id);
        $category->delete();
        return redirect()->route('vehicle-document-categories.index')->with('success', 'Category deleted successfully.');
    }
}
