<?php

namespace App\Http\Controllers\FacilitiesManagement\Rent;

use App\Http\Controllers\Controller;
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
            ->rawColumns(['actions'])
            ->make(true);
    }
    public function index()
    {
        return view('FacilitiesManagement.Rent.index');
    }

    public function create()
    {
        return view('FacilitiesManagement.Rent.create');
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
                \App\Models\SecurityDeposit::create($deposit);
            }
        }
        return redirect()->route('rent.index')->with('success', 'Rent created successfully.');
    }

    public function edit($id)
    {
        $base = RentBase::with('increments')->findOrFail($id);
        return view('FacilitiesManagement.Rent.edit', compact('base'));
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
