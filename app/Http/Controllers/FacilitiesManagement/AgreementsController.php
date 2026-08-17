<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Models\GenericDocument;
use App\Models\TableSetting;
use App\Models\Vendor;

class AgreementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-agreement|edit-agreement|delete-agreement', ['only' => ['index', 'show', 'getHistory']]);
        $this->middleware('permission:create-agreement', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-agreement', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-agreement', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $globalSettings = TableSetting::where('table_identifier', 'agreements_table')->first();
        $tableConfig = $globalSettings ? $globalSettings->settings : null;

        if ($request->ajax()) {
            $query = Agreement::with('vendor')->orderBy('id', 'desc')->get();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('vendor', function($row) { return $row->vendor ? $row->vendor->name : '-'; })
                ->addColumn('agreement_date', function($row) { return $row->agreement_date; })
                ->addColumn('from_date', function($row) { return $row->from_date; })
                ->addColumn('to_date', function($row) { return $row->to_date; })
                ->editColumn('status', function ($row) {
                    $badge = '<span class="badge bg-' . ($row->status == 1 ? 'success' : 'danger') . '">' . (($row->status == 1) ? 'Active' : 'Inactive') . '</span>';
                    return $badge;
                })
                ->addColumn('remarks', function($row) { return $row->remarks; })
                ->addColumn('actions', function ($agreement) {
                    return view('FacilitiesManagement.Agreements.partials.actions', compact('agreement'))->render();
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }
        return view('FacilitiesManagement.Agreements.index', compact('tableConfig'));
    }

    public function show($id)
    {
        $agreement = Agreement::with('vendor')->findOrFail($id);
        return view('FacilitiesManagement.Agreements.show', compact('agreement'));
    }

    public function create()
    {   
        $documents = GenericDocument::with('category')->get();
        $vendors = Vendor::forModule('facilities')->active()->orderBy('name')->get();
        $agreement = new Agreement();
        return view('FacilitiesManagement.Agreements.create', compact('agreement','documents', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $data = [
            'agreement_ref_no' => $request->agreement_ref_no,
            'vendor_id' => $request->vendor_id,
            'agreement_date' => $request->agreement_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        $agreement = Agreement::create($data);

        if ($request->input('submit_action') === 'save_and_add_attachment') {
            return redirect()
                ->route('agreements.edit', $agreement->id)
                ->with('success', 'Agreement created successfully. You can add an attachment now.');
        }

        return redirect()->route('agreements.index')->with('success', 'Agreement created successfully.');
    }

    public function edit($id)
    {
        $documents = GenericDocument::with('category')->get();
        $vendors = Vendor::forModule('facilities')->active()->orderBy('name')->get();
        $agreement = Agreement::findOrFail($id);
        return view('FacilitiesManagement.Agreements.edit', compact('agreement', 'documents', 'vendors'));
    }

    public function update(Request $request, $id)
    {
        $agreement = Agreement::findOrFail($id);
        $validated = $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $data = [
            'agreement_ref_no' => $request->agreement_ref_no,
            'vendor_id' => $request->vendor_id,
            'agreement_date' => $request->agreement_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        $agreement->update($data);

        if ($request->filled('generic_document_id')) {

            $original = GenericDocument::find($request->generic_document_id);

            if ($original) {
                if ($original->documentable_type === Agreement::class && (int) $original->documentable_id === (int) $agreement->id) {
                    return redirect()->route('agreements.index')->with('success', 'Agreement updated successfully.');
                }

                // Duplicate the record
                $duplicate = $original->replicate();

                // Modify fields as needed
                $duplicate->documentable_type = Agreement::class;
                $duplicate->documentable_id = $agreement->id;
                $duplicate->created_at = now();
                $duplicate->updated_at = now();

                $duplicate->push();
            }
        }
        return redirect()->route('agreements.index')->with('success', 'Agreement updated successfully.');
    }

    public function destroy($id)
    {
        $agreement = Agreement::findOrFail($id);
        $agreement->delete();
        return redirect()->route('agreements.index')->with('success', 'Agreement deleted successfully.');
    }


    public function getHistory($id)
    {
        $history = Helpers::getHistory(Agreement::class, $id);
        return $history;
    }
}
