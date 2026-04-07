<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Models\GenericDocument;

class AgreementsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Agreement::query();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('agreement_date', function($row) { return $row->agreement_date; })
                ->addColumn('from_date', function($row) { return $row->from_date; })
                ->addColumn('to_date', function($row) { return $row->to_date; })
                ->editColumn('status', function ($row) {
                    $badge = '<span class="badge bg-' . ($row->status == 1 ? 'success' : 'danger') . '">' . (($row->status == 1) ? 'Active' : 'Inactive') . '</span>';
                    return $badge;
                })
                ->addColumn('remarks', function($row) { return $row->remarks; })
                ->addColumn('actions', function ($agreement) {
                    $viewBtn = '<a href="' . route('agreements.show', $agreement->id) . '" class="btn btn-sm btn-info">View</a> ';
                    $editDelete = view('FacilitiesManagement.Agreements.partials.actions', compact('agreement'))->render();
                    return $viewBtn . $editDelete;
                })
                ->rawColumns(['actions', 'status'])
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
        $documents = GenericDocument::with('category')->get();
        $agreement = new Agreement();
        return view('FacilitiesManagement.Agreements.create', compact('agreement','documents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            // Add other fields as needed
        ]);

        $data = [
            'agreement_ref_no' => $request->agreement_ref_no,
            'agreement_date' => $request->agreement_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        Agreement::create($data);
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

        $data = [
            'agreement_ref_no' => $request->agreement_ref_no,
            'agreement_date' => $request->agreement_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        $agreement->update($data);
        return redirect()->route('agreements.index')->with('success', 'Agreement updated successfully.');
    }

    public function destroy($id)
    {
        $agreement = Agreement::findOrFail($id);
        $agreement->delete();
        return redirect()->route('agreements.index')->with('success', 'Agreement deleted successfully.');
    }
}
