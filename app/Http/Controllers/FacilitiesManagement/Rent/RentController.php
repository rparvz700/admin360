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
use App\Services\RentComponentCalculator;

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
        $agreements = Agreement::with('floors')->where('status', 1)->get();
        $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();
        $componentTypes = RentComponentCalculator::COMPONENTS;
        $taxableAreaSft = (float) config('facilities.rent_taxable_area_sft', 150);
        $utilityTypes = \App\Models\UtilityType::where('is_active', true)->get();

        return view('FacilitiesManagement.Rent.create', compact('agreements', 'vatTax', 'componentTypes', 'taxableAreaSft', 'utilityTypes'));
    }

    public function store(Request $request, RentComponentCalculator $calculator)
    {
        $securityDepositError = $this->validateSecurityDepositRequirement($request);
        if ($securityDepositError) {
            return back()->withInput()->withErrors(['deposits' => $securityDepositError]);
        }

        $vatTax = VatTax::where('type', 'rent')
            ->where('status', 1)
            ->firstOrFail();

        $componentRows = $calculator->rowsFromRequest($request, $vatTax);
        $totals = $calculator->totals($componentRows);
        $baseRent = $totals['base_rent'];

        $base = RentBase::create([
            'agreement_id' => $request->agreement_id,
            'base_rent'    => $baseRent,
            'vat'          => $totals['vat'],
            'tax'          => $totals['tax'],
            'is_at_source' => $request->is_at_source,
            'rent_type'    => $request->rent_type,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'remarks'      => $request->remarks,
        ]);
        $calculator->saveRows($base->id, $componentRows);

        if ($request->has('utilities')) {
            foreach ($request->input('utilities', []) as $typeId => $utilData) {
                $amount = is_numeric($utilData['amount'] ?? null) ? (float) $utilData['amount'] : 0.00;
                \App\Models\AgreementUtility::updateOrCreate(
                    [
                        'agreement_id' => $request->agreement_id,
                        'utility_type_id' => $typeId,
                    ],
                    [
                        'amount' => $amount,
                        'disburse_with_rent' => isset($utilData['disburse_with_rent']),
                    ]
                );
            }
        }
        
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
        $base = RentBase::with(['increments', 'components', 'agreement.floors'])->findOrFail($id);
        $agreements = Agreement::with('floors')->where('status', 1)->get();
        $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();
        $componentTypes = RentComponentCalculator::COMPONENTS;
        $taxableAreaSft = (float) config('facilities.rent_taxable_area_sft', 150);
        $utilityTypes = \App\Models\UtilityType::where('is_active', true)->get();
        $agreementUtilities = \App\Models\AgreementUtility::where('agreement_id', $base->agreement_id)
            ->get()
            ->keyBy('utility_type_id');

        return view('FacilitiesManagement.Rent.edit', compact('base', 'agreements', 'vatTax', 'componentTypes', 'taxableAreaSft', 'utilityTypes', 'agreementUtilities'));
    }

    public function update(Request $request, $id, RentComponentCalculator $calculator)
    {
        $securityDepositError = $this->validateSecurityDepositRequirement($request);
        if ($securityDepositError) {
            return back()->withInput()->withErrors(['deposits' => $securityDepositError]);
        }

        $vatTax = VatTax::where('type', 'rent')
            ->where('status', 1)
            ->firstOrFail();

        $componentRows = $calculator->rowsFromRequest($request, $vatTax);
        $totals = $calculator->totals($componentRows);
        $baseRent = $totals['base_rent'];

        $base = RentBase::findOrFail($id);
        $base->update(
            [
                'agreement_id' => $request->agreement_id,
                'base_rent'    => $baseRent,
                'vat'          => $totals['vat'],
                'tax'          => $totals['tax'],
                'is_at_source' => $request->is_at_source,
                'rent_type'    => $request->rent_type,
                'start_date'   => $request->start_date,
                'end_date'     => $request->end_date,
                'remarks'      => $request->remarks,
            ]
        );
        $base->components()->delete();
        $calculator->saveRows($base->id, $componentRows);

        // Update agreement utilities and log changes
        $agreementId = $base->agreement_id;
        $agreement = \App\Models\Agreement::findOrFail($agreementId);
        $oldUtilities = \App\Models\AgreementUtility::where('agreement_id', $agreementId)
            ->get()
            ->keyBy('utility_type_id');

        $oldLogValues = [];
        $newLogValues = [];

        $submittedUtilities = $request->input('utilities', []);
        $submittedIds = array_keys($submittedUtilities);

        // Delete removed utilities and log them
        foreach ($oldUtilities as $typeId => $oldUtil) {
            if (!in_array($typeId, $submittedIds)) {
                $utilityType = \App\Models\UtilityType::find($typeId);
                $typeName = $utilityType ? $utilityType->name : "Utility #$typeId";

                $oldLogValues["{$typeName} Amount"] = number_format((float) $oldUtil->amount, 2);
                $newLogValues["{$typeName} Amount"] = 'Removed';

                $oldLogValues["{$typeName} Disburse With Rent"] = $oldUtil->disburse_with_rent ? 'Yes' : 'No';
                $newLogValues["{$typeName} Disburse With Rent"] = 'Removed';

                $oldUtil->delete();
            }
        }

        // Save new/updated utilities and log them
        foreach ($submittedUtilities as $typeId => $utilData) {
            $newAmount = is_numeric($utilData['amount'] ?? null) ? (float) $utilData['amount'] : 0.00;
            $newDisburse = isset($utilData['disburse_with_rent']);

            $oldUtil = $oldUtilities->get($typeId);
            $oldAmount = $oldUtil ? (float) $oldUtil->amount : 0.00;
            $oldDisburse = $oldUtil ? (bool) $oldUtil->disburse_with_rent : false;

            if ($newAmount !== $oldAmount || $newDisburse !== $oldDisburse) {
                $utilityType = \App\Models\UtilityType::find($typeId);
                $typeName = $utilityType ? $utilityType->name : "Utility #$typeId";

                \App\Models\AgreementUtility::updateOrCreate(
                    [
                        'agreement_id' => $agreementId,
                        'utility_type_id' => $typeId,
                    ],
                    [
                        'amount' => $newAmount,
                        'disburse_with_rent' => $newDisburse,
                    ]
                );

                if ($newAmount !== $oldAmount) {
                    $oldLogValues["{$typeName} Amount"] = number_format($oldAmount, 2);
                    $newLogValues["{$typeName} Amount"] = number_format($newAmount, 2);
                }
                if ($newDisburse !== $oldDisburse) {
                    $oldLogValues["{$typeName} Disburse With Rent"] = $oldDisburse ? 'Yes' : 'No';
                    $newLogValues["{$typeName} Disburse With Rent"] = $newDisburse ? 'Yes' : 'No';
                }
            }
        }

        if (!empty($newLogValues)) {
            activity()
                ->performedOn($agreement)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $newLogValues,
                    'old' => $oldLogValues
                ])
                ->log('updated');
        }

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
        $base = RentBase::with(['components', 'increments', 'securityDeposits', 'agreement.utilities.utilityType'])->findOrFail($id);
        return view('FacilitiesManagement.Rent.show', compact('base'));
    }

    public function getHistory($id)
    {
        $rentBase = RentBase::findOrFail($id);
        
        // 1. Fetch RentBase activities
        $rentActivities = $rentBase->activities()->with('causer')->latest()->get();

        // 2. Fetch Agreement activities containing utility changes
        $agreementActivities = collect();
        if ($rentBase->agreement_id) {
            $agreement = Agreement::find($rentBase->agreement_id);
            if ($agreement) {
                $rawAgreementActivities = $agreement->activities()->with('causer')->latest()->get();
                $agreementActivities = $rawAgreementActivities->filter(function ($activity) {
                    $attributes = $activity->changes['attributes'] ?? [];
                    foreach ($attributes as $key => $value) {
                        if (str_contains($key, 'Amount') || str_contains($key, 'Disburse With Rent')) {
                            return true;
                        }
                    }
                    return false;
                });
            }
        }
        
        // 3. Merge both collections and sort by created_at desc
        $allActivities = $rentActivities->concat($agreementActivities)->sortByDesc('created_at');
        
        // 4. Map them using formatting consistent with Helpers::getHistory
        $history = $allActivities->map(function ($activity) {
            $details = [];
            $newValues = $activity->changes['attributes'] ?? [];
            $oldValues = $activity->changes['old'] ?? [];

            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? null;

                // Filter out non-utility fields for Agreement activities
                if ($activity->subject_type === Agreement::class) {
                    if (!str_contains($field, 'Amount') && !str_contains($field, 'Disburse With Rent')) {
                        continue;
                    }
                }

                $details[] = [
                    'field' => ucfirst(str_replace('_', ' ', $field)),
                    'from'  => \App\Services\LogResolverService::resolve($field, $oldValue),
                    'to'    => \App\Services\LogResolverService::resolve($field, $newValue),
                ];
            }

            return [
                'user' => $activity->causer->name ?? 'System',
                'date' => $activity->created_at->format('d M Y, h:i A'),
                'changes' => $details
            ];
        })->values();

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
