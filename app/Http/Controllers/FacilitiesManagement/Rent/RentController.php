<?php

namespace App\Http\Controllers\FacilitiesManagement\Rent;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Illuminate\Http\Request;
use App\Models\RentBase;
use App\Models\RentIncrement;
use App\Models\TableSetting;
use App\Models\VatTax;

class RentController extends Controller
{
    public function list(Request $request)
    {
        $query = RentBase::with('agreement')->orderBy('id', 'desc');
        return datatables()->of($query)
            ->addIndexColumn()
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
            ->editColumn('status', function ($row) {
                if (!$row->agreement) {
                    return '<span class="badge bg-secondary">N/A</span>';
                }

                return '<span class="badge bg-' . ($row->agreement->status == 1 ? 'success' : 'danger') . '">' . (($row->agreement->status == 1) ? 'Active' : 'Inactive') . '</span>';
            })
            ->filterColumn('agreement', function ($query, $keyword) {
                $query->whereHas('agreement', function ($q) use ($keyword) {
                    $q->where('agreement_ref_no', 'like', "%{$keyword}%");
                });
            })
            
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }
    public function index()
    {
        $globalSettings = TableSetting::where('table_identifier', 'rent_table')->first();
        $tableConfig = $globalSettings ? $globalSettings->settings : null;

        return view('FacilitiesManagement.Rent.index', compact('tableConfig'));
    }

    public function create()
    {
        $agreements = Agreement::where('status', 1)->get();

        return view('FacilitiesManagement.Rent.create', compact('agreements'));
    }

    public function store(Request $request)
    {
        $securityDepositError = $this->validateSecurityDepositRequirement($request);
        if ($securityDepositError) {
            return back()->withInput()->withErrors(['deposits' => $securityDepositError]);
        }

        $vatTax = VatTax::where('type', 'rent')
            ->where('status', 1)
            ->firstOrFail();

        $baseRent = $request->base_rent;

        $vatPercent = $vatTax->vat;
        $taxPercent = $vatTax->tax;

        $vatAmount = ($baseRent * $vatPercent) / 100;
        $taxAmount = ($baseRent * $taxPercent) / 100;

        $base = RentBase::create([
            'agreement_id' => $request->agreement_id,
            'base_rent'    => $baseRent,
            'vat'          => $vatAmount,
            'tax'          => $taxAmount,
            'is_at_source' => $request->is_at_source,
            'rent_type'    => $request->rent_type,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'remarks'      => $request->remarks,
        ]);
        
        $runningRent = (float) $baseRent;
        if ($request->has('increments')) {
            foreach ($request->increments as $increment) {
                $increment['base_rent_id'] = $base->id;
                $incrementAmount = $this->moneyValue($increment['increment_amount'] ?? null);
                $runningRent += $incrementAmount;
                RentIncrement::create([
                    'agreement_id' => $request->agreement_id,
                    'base_rent_id' => $base->id,
                    'incremented_amount' => $runningRent,
                    'increment_start_date' => $increment['increment_start_date'] ?? null,
                    'increment_end_date' => $increment['increment_end_date'] ?? null,
                    'increment_amount' => $increment['increment_amount'] ?? null,
                    'increment_percentage' => $increment['increment_percentage'] ?? null,
                    'increment_frequency' => $increment['increment_frequency'] ?? null,
                    'method_description' => $increment['method_description'] ?? null,
                ]);
            }
        }
        $this->saveSecurityDeposits($request, $base->agreement_id);
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
        $securityDepositError = $this->validateSecurityDepositRequirement($request);
        if ($securityDepositError) {
            return back()->withInput()->withErrors(['deposits' => $securityDepositError]);
        }

        $vatTax = VatTax::where('type', 'rent')
            ->where('status', 1)
            ->firstOrFail();

        $baseRent = $request->base_rent;

        $vatPercent = $vatTax->vat;
        $taxPercent = $vatTax->tax;

        $vatAmount = ($baseRent * $vatPercent) / 100;
        $taxAmount = ($baseRent * $taxPercent) / 100;

        $base = RentBase::findOrFail($id);
        $base->update(
            [
                'agreement_id' => $request->agreement_id,
                'base_rent'    => $baseRent,
                'vat'          => $vatAmount,
                'tax'          => $taxAmount,
                'is_at_source' => $request->is_at_source,
                'rent_type'    => $request->rent_type,
                'start_date'   => $request->start_date,
                'end_date'     => $request->end_date,
                'remarks'      => $request->remarks,
            ]
        );
        $base->increments()->delete();
        $runningRent = (float) $baseRent;
        if ($request->has('increments')) {
            foreach ($request->increments as $increment) {
                $increment['base_rent_id'] = $base->id;
                $incrementAmount = $this->moneyValue($increment['increment_amount'] ?? null);
                $runningRent += $incrementAmount;
                RentIncrement::create([
                    'agreement_id' => $request->agreement_id,
                    'base_rent_id' => $base->id,
                    'incremented_amount' => $runningRent,
                    'increment_start_date' => $increment['increment_start_date'] ?? null,
                    'increment_end_date' => $increment['increment_end_date'] ?? null,
                    'increment_amount' => $increment['increment_amount'] ?? null,
                    'increment_percentage' => $increment['increment_percentage'] ?? null,
                    'increment_frequency' => $increment['increment_frequency'] ?? null,
                    'method_description' => $increment['method_description'] ?? null,
                ]);
            }
        }
        \App\Models\SecurityDeposit::where('agreement_id', $base->agreement_id)->delete();
        $this->saveSecurityDeposits($request, $base->agreement_id);
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

    public function getHistory($id)
    {
        $history = Helpers::getHistory(RentBase::class, $id);
        return $history;
    }

    private function validateSecurityDepositRequirement(Request $request): ?string
    {
        $hasAbsorbable = $this->moneyValue($request->security_deposit_absorbable) > 0;
        $hasNonAbsorbable = $this->moneyValue($request->security_deposit_non_absorbable) > 0;

        if (($hasAbsorbable || $hasNonAbsorbable) && !$this->hasDepositRows($request)) {
            return 'Please add at least one deposit schedule row when Absorbable or Non-Absorbable amount is entered.';
        }

        return null;
    }

    private function saveSecurityDeposits(Request $request, int $agreementId): void
    {
        $summary = [
            'agreement_id' => $agreementId,
            'security_deposit_total' => $request->security_deposit_total,
            'security_deposit_absorbable' => $request->security_deposit_absorbable,
            'security_deposit_non_absorbable' => $request->security_deposit_non_absorbable,
        ];

        if ($this->hasDepositRows($request)) {
            foreach ($request->input('deposits', []) as $deposit) {
                \App\Models\SecurityDeposit::create(array_merge($this->depositPayload($deposit), $summary));
            }
            return;
        }

        if ($this->hasSecurityDepositSummary($request)) {
            \App\Models\SecurityDeposit::create($summary);
        }
    }

    private function hasDepositRows(Request $request): bool
    {
        return collect($request->input('deposits', []))
            ->contains(function ($deposit) {
                return collect($deposit)->contains(fn ($value) => $value !== null && $value !== '');
            });
    }

    private function depositPayload(array $deposit): array
    {
        return [
            'absorb_start_date' => $deposit['absorb_start_date'] ?? null,
            'absorb_end_date' => $deposit['absorb_end_date'] ?? null,
            'absorb_amount' => $deposit['absorb_amount'] ?? null,
            'absorb_frequency' => $deposit['month_interval'] ?? null,
            'method_description' => $deposit['method_description'] ?? null,
        ];
    }

    private function hasSecurityDepositSummary(Request $request): bool
    {
        return $this->moneyValue($request->security_deposit_total) > 0
            || $this->moneyValue($request->security_deposit_absorbable) > 0
            || $this->moneyValue($request->security_deposit_non_absorbable) > 0;
    }

    private function moneyValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
