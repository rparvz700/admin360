<?php

namespace App\Http\Controllers\FacilitiesManagement\Rent;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Illuminate\Http\Request;
use App\Models\RentBase;
use App\Models\RentIncrement;

class RentController extends Controller
{
    public function list(Request $request)
    {
        $query = RentBase::with('agreement');
        return datatables()->of($query)
            ->addColumn('agreement_start_date', function($row) {
                return $row->agreement_start_date;
            })
            ->addColumn('agreement_end_date', function($row) {
                return $row->agreement_end_date;
            })
            ->addColumn('actions', function($row) {
                return view('FacilitiesManagement.Rent.partials.actions', compact('row'))->render();
            })
            ->addColumn('agreement', function($row) {
                return ($row->agreement ? $row->agreement->agreement_ref_no : '');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    public function index()
    {
        return view('FacilitiesManagement.Rent.index');
    }

    public function create()
    {
        $agreements = Agreement::where('status', 1)->get();

        return view('FacilitiesManagement.Rent.create', compact('agreements'));
    }

    public function store(Request $request)
    {
        $base = RentBase::create($request->only(['agreement_id', 'base_rent', 'vat', 'tax', 'is_at_source', 'rent_type', 'start_date', 'end_date', 'remarks']));
        if ($request->has('increments')) {
            foreach ($request->increments as $increment) {
                $increment['base_rent_id'] = $base->id;
                RentIncrement::create([
                    'agreement_id' => $request->agreement_id,
                    'base_rent_id' => $base->id,
                    'incremented_amount' => $increment['increment_amount'] ?? null,
                    'increment_start_date' => $increment['increment_start_date'] ?? null,
                    'increment_end_date' => $increment['increment_end_date'] ?? null,
                    'increment_amount' => $increment['increment_amount'] ?? null,
                    'increment_percentage' => $increment['increment_percentage'] ?? null,
                    'increment_frequency' => $increment['increment_frequency'] ?? null,
                    'method_description' => $increment['method_description'] ?? null,
                ]);
            }
        }
        // Handle Security Deposits
        if ($request->has('deposits')) {
            foreach ($request->deposits as $deposit) {
                $deposit['agreement_id'] = $base->agreement_id;
                $deposit['security_deposit_total'] = $request->security_deposit_total;
                $deposit['security_deposit_absorbable'] = $request->security_deposit_absorbable;
                $deposit['security_deposit_non_absorbable'] = $request->security_deposit_non_absorbable;
                \App\Models\SecurityDeposit::create($deposit);
            }
        }
        return redirect()->route('rent.index')->with('success', 'Rent created successfully.');
    }

    public function edit($id)
    {
        $base = RentBase::with('increments')->findOrFail($id);
        $agreements = Agreement::where('status', 1)->get();

        return view('FacilitiesManagement.Rent.edit', compact('base', 'agreements'));
    }

    public function update(Request $request, $id)
    {
        $base = RentBase::findOrFail($id);
        $base->update($request->only(['agreement_id', 'base_rent', 'vat', 'tax', 'is_at_source', 'rent_type', 'start_date', 'end_date', 'remarks']));
        $base->increments()->delete();
        if ($request->has('increments')) {
            foreach ($request->increments as $increment) {
                $increment['base_rent_id'] = $base->id;
                RentIncrement::create([
                    'agreement_id' => $request->agreement_id,
                    'base_rent_id' => $base->id,
                    'incremented_amount' => $increment['increment_amount'] ?? null,
                    'increment_start_date' => $increment['increment_start_date'] ?? null,
                    'increment_end_date' => $increment['increment_end_date'] ?? null,
                    'increment_amount' => $increment['increment_amount'] ?? null,
                    'increment_percentage' => $increment['increment_percentage'] ?? null,
                    'increment_frequency' => $increment['increment_frequency'] ?? null,
                    'method_description' => $increment['method_description'] ?? null,
                ]);
            }
        }
        // Handle Security Deposits
        \App\Models\SecurityDeposit::where('agreement_id', $base->agreement_id)->delete();
        if ($request->has('deposits')) {
            foreach ($request->deposits as $deposit) {
                $deposit['agreement_id'] = $base->agreement_id;
                $deposit['security_deposit_total'] = $request->security_deposit_total;
                $deposit['security_deposit_absorbable'] = $request->security_deposit_absorbable;
                $deposit['security_deposit_non_absorbable'] = $request->security_deposit_non_absorbable;
                \App\Models\SecurityDeposit::create($deposit);
            }
        }
        return redirect()->route('rent.index')->with('success', 'Rent updated successfully.');
    }

    public function destroy($id)
    {
        $base = RentBase::findOrFail($id);
        $base->increments()->delete();
        $base->delete();
        return redirect()->route('rent.index')->with('success', 'Rent deleted successfully.');
    }

    public function show($id)
    {
        $base = RentBase::with(['increments', 'securityDeposits'])->findOrFail($id);
        return view('FacilitiesManagement.Rent.show', compact('base'));
    }
}
