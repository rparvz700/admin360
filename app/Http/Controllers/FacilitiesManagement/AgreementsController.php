<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class AgreementsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Agreement::query();
            return DataTables::of($query)
                ->addColumn('agreement_date', function($row) { return $row->agreement_date; })
                ->addColumn('from_date', function($row) { return $row->from_date; })
                ->addColumn('to_date', function($row) { return $row->to_date; })
                ->addColumn('status', function($row) { return $row->status; })
                ->addColumn('remarks', function($row) { return $row->remarks; })
                ->addColumn('actions', function ($agreement) {
                    $viewBtn = '<a href="' . route('agreements.show', $agreement->id) . '" class="btn btn-sm btn-info">View</a> ';
                    $editDelete = view('FacilitiesManagement.Agreements.partials.actions', compact('agreement'))->render();
                    return $viewBtn . $editDelete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('FacilitiesManagement.Agreements.index');
    }

    public function show($id)
    {
        $agreement = Agreement::findOrFail($id);
        return view('FacilitiesManagement.Agreements.show', compact('agreement'));
    }

    public function create()
    {
        $agreement = new Agreement();
        return view('FacilitiesManagement.Agreements.create', compact('agreement'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            // Add other fields as needed
        ]);
        Agreement::create($validated);
        return redirect()->route('agreements.index')->with('success', 'Agreement created successfully.');
    }

    public function edit($id)
    {
        $agreement = Agreement::findOrFail($id);
        return view('FacilitiesManagement.Agreements.edit', compact('agreement'));
    }

    public function update(Request $request, $id)
    {
        $agreement = Agreement::findOrFail($id);
        $validated = $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            // Add other fields as needed
        ]);
        $agreement->update($validated);
        return redirect()->route('agreements.index')->with('success', 'Agreement updated successfully.');
    }

    public function destroy($id)
    {
        $agreement = Agreement::findOrFail($id);
        $agreement->delete();
        return redirect()->route('agreements.index')->with('success', 'Agreement deleted successfully.');
    }
}
