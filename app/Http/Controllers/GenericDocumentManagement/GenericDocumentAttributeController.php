<?php

namespace App\Http\Controllers\GenericDocumentManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GenericDocumentAttribute;
use App\Models\GenericDocumentCategory;

class GenericDocumentAttributeController extends Controller
{
    public function index()
    {
        return view('GenericDocumentManagement.GenericDocumentAttributes.index');
    }

    public function list(Request $request)
    {
        $query = GenericDocumentAttribute::with('category');
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
        $total = GenericDocumentAttribute::count();
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
                'actions' => view('GenericDocumentManagement.GenericDocumentAttributes.partials.actions', compact('attribute'))->render(),
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
        $categories = GenericDocumentCategory::all();
        return view('GenericDocumentManagement.GenericDocumentAttributes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:generic_document_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required|in:string,number,date,boolean,select',
            'options' => 'nullable',
        ]);
        GenericDocumentAttribute::create($validated);
        return redirect()->route('generic-document-attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function show($id)
    {
        $attribute = GenericDocumentAttribute::with('category')->findOrFail($id);
        return view('GenericDocumentManagement.GenericDocumentAttributes.show', compact('attribute'));
    }

    public function edit($id)
    {
        $attribute = GenericDocumentAttribute::findOrFail($id);
        $categories = GenericDocumentCategory::all();
        return view('GenericDocumentManagement.GenericDocumentAttributes.edit', compact('attribute', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $attribute = GenericDocumentAttribute::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:generic_document_categories,id',
            'attribute_name' => 'required',
            'attribute_type' => 'required|in:string,number,date,boolean,select',
            'options' => 'nullable',
        ]);
        $attribute->update($validated);
        return redirect()->route('generic-document-attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy($id)
    {
        $attribute = GenericDocumentAttribute::findOrFail($id);
        $attribute->delete();
        return redirect()->route('generic-document-attributes.index')->with('success', 'Attribute deleted successfully.');
    }
}
