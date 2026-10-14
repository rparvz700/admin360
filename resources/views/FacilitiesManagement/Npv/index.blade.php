@extends('Partials.app', ['activeMenu' => 'facilities.npv'])

@section('title')
    NPV Calculation - {{ config('app.name') }}
@endsection

@section('page_title')
    Net Present Value (NPV) Calculation
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-fixedcolumns/fixedColumns.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/npv-calculation.css') }}">
@endsection

@section('content')
    <div class="content">
        <!-- Document / Report Title Header -->
        <div class="npv-report-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <div class="npv-report-eyebrow"><i class="fa fa-calculator me-1"></i> Facilities Management &bull; Financial
                    Analytics</div>
                <h2 class="npv-report-title">Net Present Value (NPV) Calculation Workbench</h2>
                <p class="npv-report-sub">Comprehensive lease cash outflow analysis, compounded rent escalations, advance
                    deductions, and discounted present values.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('facilities.npv.report') }}" class="btn npv-header-btn"
                    title="View Net Present Value (NPV) Valuation Report">
                    <i class="fa fa-chart-bar me-1 text-primary"></i> Valuation Report
                </a>
                {{-- <a href="{{ route('admin.finance-settings.index') }}" class="btn npv-header-btn"
                    title="Configure Default Annual Interest Rate">
                    <i class="fa fa-cog me-1 text-muted"></i> Rate Settings
                </a> --}}
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                <i class="fa fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Notice Alert if Selected Agreement has no Interest Rate configured -->
        <div id="dynamicNoRateAlert"
            class="alert alert-warning d-flex align-items-center justify-content-between p-3 my-3 {{ $selectedAgreement && !$hasConfiguredRate ? '' : 'd-none' }}"
            role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle fa-2x text-warning me-3"></i>
                <div>
                    <div class="fw-bold fs-sm">NPV Interest Rate Not Configured</div>
                    <div class="fs-xs text-muted" id="noRateAlertMsg">
                        Agreement <strong>{{ $selectedAgreement?->agreement_ref_no }}</strong> does not have an NPV interest
                        rate configured. NPV values cannot be calculated.
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('facilities.npv.report') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-sliders-h me-1"></i> Set Rate in Valuation Report
                </a>
            </div>
        </div>

        <!-- Integrated Parameter Controls Toolbar -->
        <div class="npv-controls-bar">
            <form action="{{ route('facilities.npv.calculate') }}" method="POST" id="npvForm">
                @csrf
                <div class="row g-3 align-items-start">
                    <!-- Select Agreement -->
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label">Property Lease Agreement <span class="text-danger">*</span></label>
                        <select name="agreement_id" id="agreement_id" class="form-select js-select2"
                            data-placeholder="-- Choose Lease Agreement --" style="width: 100%;" required>
                            <option value=""></option>
                            @foreach ($agreements as $agr)
                                @php
                                    $bName =
                                        $agr->floors->first()->building->site_name ??
                                        ($agr->floors->first()->building->code ?? 'N/A');
                                    $vendorName = $agr->vendor->name ?? 'N/A';
                                    $hasAgrRate =
                                        !is_null($agr->npv_interest_rate) && (float) $agr->npv_interest_rate > 0;
                                    $rateTag = $hasAgrRate
                                        ? number_format((float) $agr->npv_interest_rate, 2) . '%'
                                        : 'Rate Not Set';
                                @endphp
                                <option value="{{ $agr->id }}"
                                    {{ old('agreement_id', $selectedAgreementId) == $agr->id ? 'selected' : '' }}
                                    data-from="{{ $agr->agreement_date ?? ($agr->payment_start_date ?? $agr->from_date) }}"
                                    data-payment-start="{{ $agr->payment_start_date ?? $agr->from_date }}"
                                    data-to="{{ $agr->expiry_date ?? $agr->to_date }}"
                                    data-has-rate="{{ $hasAgrRate ? '1' : '0' }}"
                                    data-interest-rate="{{ $hasAgrRate ? (float) $agr->npv_interest_rate : '' }}">
                                    {{ $agr->agreement_ref_no }} | Site: {{ $bName }} ({{ $vendorName }})
                                    [{{ $rateTag }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Base Date -->
                    <div class="col-lg-3 col-md-3">
                        <label class="form-label">Calculation Base Date <span class="text-danger">*</span></label>
                        <input type="date" name="base_date" id="base_date" class="form-control"
                            value="{{ old('base_date', $defaultBaseDate) }}" required>
                    </div>

                    <!-- Annual Discount Rate -->
                    <div class="col-lg-4 col-md-3">
                        <label class="form-label">Annual Interest Rate (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.0001" min="0" max="100" name="annual_discount_rate"
                                id="annual_discount_rate" class="form-control"
                                value="{{ old('annual_discount_rate', $currentRate ?? '') }}"
                                placeholder="{{ $selectedAgreement && !$hasConfiguredRate ? 'Not configured for agreement' : 'e.g. 12.00' }}"
                                {{ $selectedAgreement && !$hasConfiguredRate ? 'disabled' : '' }} required>
                            <span class="input-group-text">%</span>
                            <button type="button" class="btn btn-outline-secondary" id="resetRateBtn"
                                title="Reset to Agreement Rate"
                                {{ $selectedAgreement && !$hasConfiguredRate ? 'disabled' : '' }}>
                                <i class="fa fa-undo"></i>
                            </button>
                        </div>
                        <div class="form-text fs-3xs mt-1" id="rateHelperText">
                            @if ($selectedAgreement && $hasConfiguredRate)
                                <span class="text-success"><i class="fa fa-check-circle me-1"></i> Agreement rate:
                                    {{ number_format($selectedAgreement->npv_interest_rate, 2) }}%</span>
                            @elseif ($selectedAgreement && !$hasConfiguredRate)
                                <span class="text-danger"><i class="fa fa-exclamation-triangle me-1"></i> No interest rate
                                    set for this agreement.</span>
                            @else
                                <span class="text-muted">Agreement-specific NPV rate.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <span class="fs-xs text-muted">
                        <i class="fa fa-info-circle me-1"></i> Tax top-up (gross-up) & VAT are automatically sourced from DB
                        <code>rent_base</code>.
                    </span>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 btn-sm" id="calculateNpvBtn"
                            {{ $selectedAgreement && !$hasConfiguredRate ? 'disabled' : '' }}>
                            <i class="fa fa-calculator me-1"></i> Calculate NPV
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($initialResult)
            @php
                // 1 -> "1st", 2 -> "2nd", 3 -> "3rd", 4 -> "4th" ...
                $npvOrdinal = function (int $n): string {
                    $suffix = 'th';
                    if (!in_array($n % 100, [11, 12, 13], true)) {
                        $suffix = match ($n % 10) {
                            1 => 'st',
                            2 => 'nd',
                            3 => 'rd',
                            default => 'th',
                        };
                    }
                    return $n . $suffix;
                };
            @endphp

            <!-- Executive Summary Strip (Single Bar Layout) -->
            <div class="npv-summary-strip">
                <div class="row g-0">
                    <div class="col-6 col-lg-3 summary-metric-item hero-metric ps-4 ps-md-5"
                        title="Total Present Value (NPV): Sum of discounted monthly net cash outflows over {{ $initialResult->totalMonths }} months">
                        <div class="metric-label" title="Total Present Value (NPV)"><i
                                class="fa fa-coins me-1 text-primary"></i> Total Present Value (NPV)</div>
                        <div class="metric-value text-npv-primary"
                            title="৳ {{ number_format($initialResult->totalNPV, 2) }}">৳
                            {{ number_format($initialResult->totalNPV, 2) }}</div>
                        <div class="metric-subtext"
                            title="Sum of discounted monthly net cash outflows over {{ $initialResult->totalMonths }} months (Discount Rate: {{ number_format($initialResult->monthlyDiscountRate * 100, 4) }}% monthly / {{ number_format($initialResult->annualDiscountRate, 2) }}% annual)">
                            Discounted Net Outflows</div>
                    </div>
                    <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                        title="Total Nominal Outflow: Undiscounted total cash outflow over lease term">
                        <div class="metric-label" title="Total Nominal Outflow"><i
                                class="fa fa-money-bill-wave me-1 text-warning"></i> Total Nominal Outflow</div>
                        <div class="metric-value text-npv-warning"
                            title="৳ {{ number_format($initialResult->totalUndiscountedOutflow, 2) }}">৳
                            {{ number_format($initialResult->totalUndiscountedOutflow, 2) }}</div>
                        <div class="metric-subtext"
                            title="Undiscounted gross cash outflow over entire lease term of {{ $initialResult->totalMonths }} months">
                            Undiscounted Cash Total</div>
                    </div>
                    <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                        title="Lease Horizon & Discount Rate: {{ $initialResult->totalMonths }} months at {{ number_format($initialResult->annualDiscountRate, 2) }}% annual discount rate">
                        <div class="metric-label" title="Lease Horizon & Discount Rate"><i
                                class="fa fa-calendar-alt me-1 text-info"></i> Lease Horizon & Rate</div>
                        <div class="metric-value text-npv-info"
                            title="{{ $initialResult->totalMonths }} months (Rate: {{ number_format($initialResult->annualDiscountRate, 2) }}% annual)">
                            {{ $initialResult->totalMonths }} <span class="fs-xs font-normal">mos</span></div>
                        <div class="metric-subtext"
                            title="Monthly Discount Rate: {{ number_format($initialResult->monthlyDiscountRate * 100, 4) }}% | Annual Rate: {{ number_format($initialResult->annualDiscountRate, 2) }}%">
                            Monthly Rate: {{ number_format($initialResult->monthlyDiscountRate * 100, 4) }}% (Annual:
                            {{ number_format($initialResult->annualDiscountRate, 2) }}%)</div>
                    </div>
                    <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                        title="Absorbable Advance & Security Deposit Summary">
                        <div class="metric-label" title="Absorbable Advance & Security Deposit"><i
                                class="fa fa-hand-holding-usd me-1 text-success"></i> Advance (Absorbable)</div>
                        <div class="metric-value text-npv-success"
                            title="৳ {{ number_format($initialResult->absorbableAdvanceTotal, 2) }}">৳
                            {{ number_format($initialResult->absorbableAdvanceTotal, 2) }}</div>
                        <div class="metric-subtext"
                            title="Total Advance Deducted in Schedule: ৳ {{ number_format($initialResult->totalAdvanceDeductions, 2) }} | Non-Absorbable Deposit: ৳ {{ number_format($initialResult->nonAbsorbableDepositTotal, 2) }}">
                            Deducted: ৳ {{ number_format($initialResult->totalAdvanceDeductions, 2) }} | SD: ৳
                            {{ number_format($initialResult->nonAbsorbableDepositTotal, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- @if ($initialResult->hasIfrs16Schedule)
                <!-- IFRS 16 Schedule Executive Summary -->
                <div class="ifrs16-container mb-4">
                    <div class="ifrs16-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-indigo text-white px-2.5 py-1.5 fs-xs font-semibold"
                                style="background-color: #4f46e5 !important;">
                                <i class="fa fa-balance-scale me-1"></i> IFRS 16 Schedule Applicable
                            </span>
                            <span class="fs-xs text-muted">
                                Total Present Value (<strong>৳ {{ number_format($initialResult->totalNPV, 2) }}</strong>)
                                is ≥ ৳ 1,000,000 threshold. Amortization and PPE schedule generated.
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border fs-3xs">
                                <i class="fa fa-percentage text-primary me-1"></i> Monthly Compounding:
                                {{ number_format($initialResult->monthlyDiscountRate * 100, 4) }}%
                            </span>
                        </div>
                    </div>

                    <!-- 7 IFRS 16 KPI Cards Grid -->
                    <div class="row g-3">
                        <!-- 1. Opening Lease Obligation -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-slate">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="Opening Lease Obligation">Opening Lease
                                        Obligation</div>
                                    <div class="ifrs16-icon icon-slate"><i class="fa fa-file-invoice-dollar"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-dark"
                                    title="৳ {{ number_format($initialResult->initialLeaseLiability, 2) }}">
                                    ৳ {{ number_format($initialResult->initialLeaseLiability, 2) }}
                                </div>
                                <p class="ifrs16-card-sub" title="Initial recognized lease liability">Initial recognized
                                    liability</p>
                            </div>
                        </div>

                        <!-- 2. Lease Payment -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-primary">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="Lease Payment">Lease Payment</div>
                                    <div class="ifrs16-icon icon-primary"><i class="fa fa-receipt"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-primary"
                                    title="৳ {{ number_format($initialResult->totalLeasePayments, 2) }}">
                                    ৳ {{ number_format($initialResult->totalLeasePayments, 2) }}
                                </div>
                                <p class="ifrs16-card-sub" title="Total principal repaid">Total principal repaid</p>
                            </div>
                        </div>

                        <!-- 3. Closing Lease Obligation -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-success">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="Closing Lease Obligation">Closing Lease
                                        Obligation</div>
                                    <div class="ifrs16-icon icon-success"><i class="fa fa-check-circle"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-success" title="৳ 0.00">
                                    ৳ 0.00
                                </div>
                                <p class="ifrs16-card-sub" title="Fully amortized at expiry">Fully amortized at expiry</p>
                            </div>
                        </div>

                        <!-- 4. Finance Expense -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-danger">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="Finance Expense">Finance Expense</div>
                                    <div class="ifrs16-icon icon-danger"><i class="fa fa-chart-pie"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-danger"
                                    title="৳ {{ number_format($initialResult->totalFinanceExpense, 2) }}">
                                    ৳ {{ number_format($initialResult->totalFinanceExpense, 2) }}
                                </div>
                                <p class="ifrs16-card-sub" title="Total interest cost over term">Total interest over term
                                </p>
                            </div>
                        </div>

                        <!-- 5. PPE Opening Balance -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-info">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="PPE Opening Balance">PPE Opening Balance</div>
                                    <div class="ifrs16-icon icon-info"><i class="fa fa-building"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-info"
                                    title="৳ {{ number_format($initialResult->initialRouAsset, 2) }}">
                                    ৳ {{ number_format($initialResult->initialRouAsset, 2) }}
                                </div>
                                <p class="ifrs16-card-sub" title="Initial Right-of-Use (ROU) asset balance">Initial ROU
                                    asset balance</p>
                            </div>
                        </div>

                        <!-- 6. Depreciation Charged -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-warning">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="Depreciation Charged">Depreciation Charged</div>
                                    <div class="ifrs16-icon icon-warning"><i class="fa fa-sort-amount-down"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-warning"
                                    title="৳ {{ number_format($initialResult->totalDepreciation, 2) }}">
                                    ৳ {{ number_format($initialResult->totalDepreciation, 2) }}
                                </div>
                                <p class="ifrs16-card-sub"
                                    title="Monthly: ৳ {{ number_format($initialResult->monthlyDepreciation, 2) }}">
                                    ৳ {{ number_format($initialResult->monthlyDepreciation, 2) }}/month
                                </p>
                            </div>
                        </div>

                        <!-- 7. WDV -->
                        <div class="col-12 col-sm-6 col-lg-4 col-xl">
                            <div class="ifrs16-card card-success">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ifrs16-card-title" title="WDV">WDV</div>
                                    <div class="ifrs16-icon icon-success"><i class="fa fa-shield-alt"></i></div>
                                </div>
                                <div class="ifrs16-card-value text-success" title="৳ 0.00">
                                    ৳ 0.00
                                </div>
                                <p class="ifrs16-card-sub" title="Fully depreciated at expiry">Fully depreciated at expiry
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif --}}

            <!-- Contract & Rent Source Reference Audit -->
            <div class="npv-source-audit mb-4">
                <div class="audit-header d-flex justify-content-between align-items-center">
                    <h3 class="audit-title">
                        <i class="fa fa-database me-1 text-muted"></i> Rent Base & Contract Source Audit (Agreement
                        #{{ $initialResult->agreement->id }} - {{ $initialResult->agreement->agreement_ref_no }})
                    </h3>
                    <button type="button" class="btn btn-sm btn-alt-secondary py-0 px-2" data-bs-toggle="collapse"
                        data-bs-target="#npvAuditContent" aria-expanded="false">
                        <i class="fa fa-chevron-down fs-xs"></i>
                    </button>
                </div>
                <div class="collapse show" id="npvAuditContent">
                    <div class="p-3 fs-xs">
                        @php
                            $latestRentBase = $initialResult->agreement->rentBases->sortByDesc('id')->first();
                            $latestSd = $initialResult->agreement->securityDeposits->sortByDesc('id')->first();
                            $increments = $initialResult->agreement->rentIncrements->sortBy('increment_start_date');
                        @endphp
                        <div class="row g-3">
                            <!-- Row 1 Left: Rent Base Parameters -->
                            <div class="col-md-6">
                                <div class="audit-data-box h-100">
                                    <div class="text-muted font-semibold mb-1">Rent Base Parameters</div>
                                    <div>Base Rent: <strong>৳
                                            {{ number_format($latestRentBase->base_rent ?? 0, 2) }}</strong></div>
                                    <div>At Source Tax:
                                        <strong>{{ $latestRentBase->is_at_source ?? false ? 'YES (1)' : 'NO (0)' }}</strong>
                                    </div>
                                    <div>VAT: ৳ {{ number_format($latestRentBase->vat ?? 0, 2) }} | Tax: ৳
                                        {{ number_format($latestRentBase->tax ?? 0, 2) }}</div>
                                    <div class="mt-1 fs-2xs text-muted">
                                        Agreement Date:
                                        <strong>{{ $initialResult->agreement->agreement_date ? \Carbon\Carbon::parse($initialResult->agreement->agreement_date)->format('d M Y') : 'N/A' }}</strong>
                                        &bull;
                                        Payment Start:
                                        <strong>{{ $initialResult->agreement->payment_start_date ?? $initialResult->agreement->from_date ? \Carbon\Carbon::parse($initialResult->agreement->payment_start_date ?? $initialResult->agreement->from_date)->format('d M Y') : 'N/A' }}</strong>
                                        to
                                        Expiry:
                                        <strong>{{ $initialResult->agreement->expiry_date ?? $initialResult->agreement->to_date ? \Carbon\Carbon::parse($initialResult->agreement->expiry_date ?? $initialResult->agreement->to_date)->format('d M Y') : 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 1 Right: Security Deposit & Advance Structure -->
                            <div class="col-md-6">
                                <div class="audit-data-box h-100">
                                    <div
                                        class="text-muted font-semibold mb-1 d-flex justify-content-between align-items-center">
                                        <span>Security Deposit & Advance Structure</span>
                                        @if ($initialResult->agreement->securityDeposits->count() > 1)
                                            <span
                                                class="badge bg-primary-light text-primary fs-3xs">{{ $initialResult->agreement->securityDeposits->count() }}
                                                Clauses</span>
                                        @endif
                                    </div>
                                    <div>Total Deposit: <strong>৳
                                            {{ number_format($initialResult->agreement->securityDeposits->max('security_deposit_total') ?? ($latestSd->security_deposit_total ?? 0), 2) }}</strong>
                                    </div>
                                    <div>Adjustable Advance: <strong>৳
                                            {{ number_format($initialResult->absorbableAdvanceTotal, 2) }}</strong></div>
                                    <div>Non-Adjustable Deposit: <strong>৳
                                            {{ number_format($initialResult->nonAbsorbableDepositTotal, 2) }}</strong>
                                    </div>

                                    @php $sdRows = $initialResult->agreement->securityDeposits->filter(fn($sd) => $sd->absorb_amount > 0 || $sd->absorb_frequency > 0); @endphp
                                    @if ($sdRows->isNotEmpty())
                                        <div class="mt-2 border-top pt-2">
                                            <div class="fw-semibold text-dark mb-1 fs-3xs">Adjustment Clauses Schedule:
                                            </div>
                                            <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered mb-0 bg-white"
                                                    style="font-size: 10px;">
                                                    <thead>
                                                        <tr class="table-secondary"
                                                            style="font-size: 10px; text-transform: uppercase;">
                                                            <th>Clause</th>
                                                            <th class="text-end">Amount</th>
                                                            <th class="text-center">Interval</th>
                                                            <th class="text-end">Monthly</th>
                                                            <th class="text-center">Period</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $prevClauseEnd = null; @endphp
                                                        @foreach ($sdRows as $idx => $sd)
                                                            @php
                                                                $amount = (float) ($sd->absorb_amount ?? 0);
                                                                $interval = (int) ($sd->absorb_frequency ?? 0);
                                                                $monthly =
                                                                    $amount > 0 && $interval > 0
                                                                        ? $amount / $interval
                                                                        : 0;

                                                                if ($sd->absorb_start_date) {
                                                                    $cStart = \Carbon\Carbon::parse(
                                                                        $sd->absorb_start_date,
                                                                    );
                                                                } elseif ($prevClauseEnd) {
                                                                    $cStart = $prevClauseEnd
                                                                        ->copy()
                                                                        ->addMonth()
                                                                        ->startOfMonth();
                                                                } else {
                                                                    $cStart = $initialResult->agreement->from_date
                                                                        ? \Carbon\Carbon::parse(
                                                                            $initialResult->agreement->from_date,
                                                                        )
                                                                        : null;
                                                                }

                                                                if ($sd->absorb_end_date) {
                                                                    $cEnd = \Carbon\Carbon::parse($sd->absorb_end_date);
                                                                } elseif ($cStart && $interval > 0) {
                                                                    $cEnd = $cStart
                                                                        ->copy()
                                                                        ->addMonths($interval - 1)
                                                                        ->endOfMonth();
                                                                } else {
                                                                    $cEnd = null;
                                                                }

                                                                if ($cEnd) {
                                                                    $prevClauseEnd = $cEnd;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>Clause #{{ $loop->iteration }}</td>
                                                                <td class="text-end">৳ {{ number_format($amount, 2) }}
                                                                </td>
                                                                <td class="text-center">{{ $interval }} mos</td>
                                                                <td class="text-end text-info fw-semibold">৳
                                                                    {{ number_format($monthly, 2) }}</td>
                                                                <td class="text-center fs-3xs">
                                                                    {{ $cStart ? $cStart->format('M Y') : 'N/A' }} -
                                                                    {{ $cEnd ? $cEnd->format('M Y') : 'N/A' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-1 fs-2xs text-muted">
                                            Interval: {{ $latestSd->absorb_frequency ?? 'N/A' }} months | Start:
                                            {{ $latestSd->absorb_start_date ?? 'N/A' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Row 2 Left: Rent Components Breakdown -->
                            <div class="col-md-6">
                                <div class="audit-data-box h-100">
                                    <div class="text-muted font-semibold mb-1">Rent Components Breakdown</div>
                                    @if ($latestRentBase && $latestRentBase->components->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 bg-white"
                                                style="font-size: 10px;">
                                                <thead>
                                                    <tr class="table-secondary"
                                                        style="font-size: 12px; text-transform: uppercase;">
                                                        <th>Type</th>
                                                        <th class="text-end">Area</th>
                                                        <th class="text-end">Rent</th>
                                                        <th class="text-end">VAT</th>
                                                        <th class="text-end">TAX</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($latestRentBase->components as $comp)
                                                        <tr>
                                                            <td><strong>{{ $comp->component_type }}</strong></td>
                                                            <td class="text-end">{{ number_format($comp->area_sft, 2) }}
                                                            </td>
                                                            <td class="text-end">৳
                                                                {{ number_format($comp->rent_amount, 2) }}</td>
                                                            <td class="text-end">৳
                                                                {{ number_format($comp->vat_amount ?? 0, 2) }}</td>
                                                            <td class="text-end">৳
                                                                {{ number_format($comp->tax_amount ?? 0, 2) }}</td>
                                                            <td class="text-end font-bold">৳
                                                                {{ number_format($comp->total_amount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <em class="text-muted">No component breakdown rows. Using base_rent fallback: ৳
                                            {{ number_format($latestRentBase->base_rent ?? 0, 2) }}</em>
                                    @endif
                                </div>
                            </div>

                            <!-- Row 2 Right: Rent Escalation Cycles -->
                            <div class="col-md-6">
                                <div class="audit-data-box h-100">
                                    <div
                                        class="text-muted font-semibold mb-1 d-flex justify-content-between align-items-center">
                                        <span>Rent Escalation Cycles</span>
                                        <span
                                            class="badge bg-primary-light text-primary fs-3xs">{{ $increments->count() }}
                                            Defined</span>
                                    </div>
                                    @if ($increments->count() > 0)
                                        <div class="table-responsive" style="max-height: 140px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0 bg-white"
                                                style="font-size: 11px;">
                                                <thead>
                                                    <tr class="table-secondary"
                                                        style="font-size: 12px; text-transform: uppercase;">
                                                        <th class="text-center" style="width: 45px;">Cycle</th>
                                                        <th class="text-center">Effective From</th>
                                                        <th class="text-end">Escalation</th>
                                                        <th class="text-end">New Rent</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($increments as $idx => $inc)
                                                        @php
                                                            $cycleOrd = $npvOrdinal($idx + 1);
                                                            $escalationStr = $inc->increment_percentage
                                                                ? number_format((float) $inc->increment_percentage, 2) .
                                                                    '%'
                                                                : ($inc->increment_amount
                                                                    ? '৳ ' .
                                                                        number_format((float) $inc->increment_amount, 2)
                                                                    : '-');
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center">
                                                                <span class="badge npv-inc-badge npv-inc-badge-new"
                                                                    style="margin-left: 0;">{{ $cycleOrd }}</span>
                                                            </td>
                                                            <td class="text-center fs-3xs">
                                                                {{ $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->format('d M Y') : '-' }}
                                                            </td>
                                                            <td class="text-end fw-semibold text-warning fs-3xs">
                                                                {{ $escalationStr }}</td>
                                                            <td class="text-end num-cell font-bold fs-3xs">৳
                                                                {{ number_format((float) ($inc->incremented_amount ?? 0), 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <em class="text-muted fs-3xs">No rent escalation cycles configured. Base rent
                                            remains constant.</em>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Focal Point: Detailed Monthly Cash Outflow & PV Breakdown Table -->
            <div class="npv-report-section">
                <div class="npv-section-header">
                    <h3 class="npv-section-title">
                        <i class="fa fa-table text-primary"></i> Monthly Cash Outflow & Present Value Schedule
                        ({{ $initialResult->totalMonths }} Months)
                    </h3>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-switch mb-0 npv-breakdown-toggle"
                            title="Show/hide per-space (Office/DG/Parking/Store) gross rent breakdown columns">
                            <input class="form-check-input" type="checkbox" role="switch" id="npvToggleBreakdown">
                            <label class="form-check-label" for="npvToggleBreakdown">
                                <i class="fa fa-list-ul me-1"></i> Space Breakdown
                            </label>
                        </div>
                        @if ($initialResult->hasIfrs16Schedule)
                            <div class="form-check form-switch mb-0 npv-ifrs16-toggle"
                                title="Show/hide IFRS 16 Lease Obligation & Asset schedule columns">
                                <input class="form-check-input" type="checkbox" role="switch" id="npvToggleIfrs16"
                                    checked>
                                <label class="form-check-label" for="npvToggleIfrs16">
                                    <i class="fa fa-balance-scale me-1 text-primary"></i> IFRS 16 Schedule
                                </label>
                            </div>
                        @endif
                        <a href="{{ route('facilities.npv.export', ['agreement_id' => $initialResult->agreement->id, 'base_date' => $initialResult->baseDate, 'annual_discount_rate' => $initialResult->annualDiscountRate]) }}"
                            class="btn btn-sm btn-outline-success">
                            <i class="fa fa-file-excel me-1"></i> Export Schedule (Excel)
                        </a>
                    </div>
                </div>



                @if (($initialResult->cashFlows[0]->totalIncrementCycles ?? 0) > 0)
                    <div class="npv-inc-legend">
                        <span class="npv-inc-legend-title">Increment cycle:</span>
                        <span class="badge npv-inc-badge npv-inc-badge-new"><i class="fa fa-arrow-up"></i> 2nd</span>
                        <span>cycle takes effect this month</span>
                        <span class="npv-inc-legend-sep">|</span>
                        <span class="badge npv-inc-badge npv-inc-badge-carry"><i class="fa fa-arrow-up"></i> 2nd</span>
                        <span>cycle carried forward</span>
                        <span class="npv-inc-legend-sep">|</span>
                        <span>no badge = base rent, no increment applied yet</span>
                    </div>
                @endif

                <div class="npv-table-wrapper">
                    <table class="table table-sm table-bordered table-striped table-hover table-vcenter table-npv"
                        id="npvBreakdownTable">
                        <thead>
                            <tr>
                                <th class="text-center npv-col-left-1">#</th>
                                <th class="text-center npv-col-left-2"
                                    title="Billing month. A badge marks which rent increment cycle (1st, 2nd, 3rd...) is in force — solid on the month the cycle takes effect, faded while it is carried forward.">
                                    Billing Month</th>
                                <th class="text-end npv-col-breakdown" title="Office space gross rent for the month">
                                    Office Gross (৳)</th>
                                <th class="text-end npv-col-breakdown"
                                    title="Diesel generator (DG) gross rent for the month">DG Gross (৳)</th>
                                <th class="text-end npv-col-breakdown" title="Parking space gross rent for the month">
                                    Parking Gross (৳)</th>
                                <th class="text-end npv-col-breakdown" title="Store space gross rent for the month">Store
                                    Gross (৳)</th>
                                <th class="text-end npv-total-col"
                                    title="Sum of all space-wise gross rents for the month">Total Gross (৳)</th>
                                <th class="text-end text-danger"
                                    title="Adjustable advance deducted from this month's payment">Advance Adj. (-৳)</th>
                                <th class="text-end text-success" title="Security deposit refunded/adjusted this month">SD
                                    Refund (-৳)</th>
                                <th class="text-end fw-bold" title="Total Gross minus Advance Adj. minus SD Refund">Net
                                    Outflow (৳)</th>
                                <th class="text-center"
                                    title="Present value discount factor applied to this month's net outflow">Discount
                                    Factor</th>
                                <th class="text-end text-primary fw-bold" title="Net Outflow x Discount Factor">Present
                                    Value (NPV) (৳)</th>
                                <th class="text-end text-info" title="Running total of Present Value up to this month">
                                    Cumulative PV (৳)</th>
                                @if ($initialResult->hasIfrs16Schedule)
                                    <th class="text-end npv-col-ifrs16 npv-header-lease"
                                        title="Opening lease obligation liability balance for the month">Opening Lease
                                        Obligation (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-lease"
                                        title="Principal lease payment / repayment for the month">Lease Payment (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-lease"
                                        title="Closing lease obligation liability balance at month end">Closing Lease
                                        Obligation (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-lease"
                                        title="Finance expense / interest cost (Opening Obligation x Monthly Compounding Rate)">
                                        Finance Expense (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-rou"
                                        title="PPE Opening balance (ROU Asset balance at start of month)">PPE Opening
                                        Balance (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-rou"
                                        title="Depreciation charged for the month (Straight-line allocation)">Depreciation
                                        Charged (৳)</th>
                                    <th class="text-end npv-col-ifrs16 npv-header-rou"
                                        title="Written Down Value (WDV) of ROU Asset at month end">WDV (৳)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($initialResult->cashFlows as $cf)
                                <tr
                                    class="{{ $cf->isDeferred ? 'table-light text-muted opacity-75' : ($cf->arrearsAmount > 0 ? 'table-warning-light' : '') }}">
                                    <td class="text-center text-muted font-mono fs-xs npv-col-left-1">
                                        {{ $cf->periodIndex }}</td>
                                    <td class="text-center fw-semibold fs-xs npv-col-left-2">
                                        {{ $cf->monthLabel }}
                                        @if ($cf->isDeferred)
                                            <span class="badge bg-secondary ms-1 fs-3xs"
                                                title="No payment due this month (Deferred Payment Period)">
                                                <i class="fa fa-clock"></i> Deferred
                                            </span>
                                        @elseif ($cf->arrearsAmount > 0)
                                            <span class="badge bg-warning text-dark ms-1 fs-3xs"
                                                title="Includes ৳ {{ number_format($cf->arrearsAmount, 2) }} arrears from deferred period">
                                                <i class="fa fa-hand-holding-usd"></i> Arrears Paid
                                            </span>
                                        @endif
                                        @if ($cf->incrementCycle > 0)
                                            @php
                                                $incCurrent = collect($cf->activeIncrements)->last();
                                                $incRate =
                                                    $incCurrent['percentage'] ??
                                                    ($incCurrent['equivalent_pct'] ?? null);
                                                $incTooltip =
                                                    $npvOrdinal($cf->incrementCycle) .
                                                    ' rent increment' .
                                                    ($cf->totalIncrementCycles > 0
                                                        ? ' (cycle ' .
                                                            $cf->incrementCycle .
                                                            ' of ' .
                                                            $cf->totalIncrementCycles .
                                                            ')'
                                                        : '') .
                                                    ($cf->incrementEffectiveFrom
                                                        ? ' — effective from ' .
                                                            \Carbon\Carbon::parse($cf->incrementEffectiveFrom)->format(
                                                                'd M Y',
                                                            )
                                                        : '') .
                                                    ($incRate !== null
                                                        ? ', at ' . number_format((float) $incRate, 2) . '%'
                                                        : '') .
                                                    '. Cumulative uplift over base rent: ' .
                                                    number_format($cf->incrementUpliftPct, 2) .
                                                    '%.' .
                                                    ($cf->incrementStartsThisMonth
                                                        ? ' This cycle takes effect this month.'
                                                        : ' Carried forward into this month.');
                                            @endphp
                                            <span
                                                class="badge npv-inc-badge {{ $cf->incrementStartsThisMonth ? 'npv-inc-badge-new' : 'npv-inc-badge-carry' }}"
                                                title="{{ $incTooltip }}">
                                                <i class="fa fa-arrow-up"></i> {{ $npvOrdinal($cf->incrementCycle) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end num-cell npv-col-breakdown">
                                        {{ number_format($cf->officeGrossRent, 2) }}</td>
                                    <td class="text-end num-cell npv-col-breakdown">
                                        {{ number_format($cf->dgGrossRent, 2) }}</td>
                                    <td class="text-end num-cell npv-col-breakdown">
                                        {{ number_format($cf->parkingGrossRent, 2) }}</td>
                                    <td class="text-end num-cell npv-col-breakdown">
                                        {{ number_format($cf->storeGrossRent, 2) }}</td>
                                    <td class="text-end num-cell fw-semibold npv-total-col">
                                        {{ number_format($cf->totalGrossRent, 2) }}</td>
                                    <td class="text-end num-cell text-danger">
                                        {{ $cf->advanceDeduction > 0 ? '-' . number_format($cf->advanceDeduction, 2) : '0.00' }}
                                    </td>
                                    <td class="text-end num-cell text-success">
                                        {{ $cf->depositRefund > 0 ? '-' . number_format($cf->depositRefund, 2) : '0.00' }}
                                    </td>
                                    <td class="text-end num-cell fw-bold">{{ number_format($cf->netOutflow, 2) }}</td>
                                    <td class="text-center num-cell text-muted fs-xs">
                                        {{ number_format($cf->discountFactor, 6) }}</td>
                                    <td class="text-end num-cell text-primary fw-bold">৳
                                        {{ number_format($cf->presentValue, 2) }}</td>
                                    <td class="text-end num-cell text-info fs-xs">৳
                                        {{ number_format($cf->cumulativePV, 2) }}</td>
                                    @if ($initialResult->hasIfrs16Schedule)
                                        <td class="text-end num-cell npv-col-ifrs16 text-muted">
                                            {{ $cf->openingLeaseObligation !== null ? number_format($cf->openingLeaseObligation, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 text-success fw-semibold">
                                            {{ $cf->leasePayment !== null ? number_format($cf->leasePayment, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 fw-bold">
                                            {{ $cf->closingLeaseObligation !== null ? number_format($cf->closingLeaseObligation, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 text-danger">
                                            {{ $cf->financeExpense !== null ? number_format($cf->financeExpense, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 text-info">
                                            {{ $cf->rouOpeningBalance !== null ? number_format($cf->rouOpeningBalance, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 text-warning">
                                            {{ $cf->depreciationCharged !== null ? number_format($cf->depreciationCharged, 2) : '-' }}
                                        </td>
                                        <td class="text-end num-cell npv-col-ifrs16 fw-semibold text-primary">
                                            {{ $cf->rouWdv !== null ? number_format($cf->rouWdv, 2) : '-' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="npv-tfoot-total">
                            <tr>
                                <td class="text-center npv-col-left-1"></td>
                                <td class="text-end fw-bold npv-col-left-2">TOTALS:</td>
                                <td class="text-end npv-col-breakdown">-</td>
                                <td class="text-end npv-col-breakdown">-</td>
                                <td class="text-end npv-col-breakdown">-</td>
                                <td class="text-end npv-col-breakdown">-</td>
                                <td class="text-end num-cell fw-bold npv-total-col">৳
                                    {{ number_format($initialResult->totalGrossRent, 2) }}</td>
                                <td class="text-end num-cell text-warning fw-bold">-৳
                                    {{ number_format($initialResult->totalAdvanceDeductions, 2) }}</td>
                                <td class="text-end num-cell text-success fw-bold">-৳
                                    {{ number_format($initialResult->totalDepositRefunds, 2) }}</td>
                                <td class="text-end num-cell fw-bold">৳
                                    {{ number_format($initialResult->totalUndiscountedOutflow, 2) }}</td>
                                <td class="text-center">-</td>
                                <td class="text-end num-cell text-primary fw-bold">৳
                                    {{ number_format($initialResult->totalNPV, 2) }}</td>
                                <td class="text-end">-</td>
                                @if ($initialResult->hasIfrs16Schedule)
                                    <td class="text-end npv-col-ifrs16">-</td>
                                    <td class="text-end num-cell text-success fw-bold npv-col-ifrs16">৳
                                        {{ number_format($initialResult->totalLeasePayments, 2) }}</td>
                                    <td class="text-end num-cell fw-bold npv-col-ifrs16">৳ 0.00</td>
                                    <td class="text-end num-cell text-danger fw-bold npv-col-ifrs16">৳
                                        {{ number_format($initialResult->totalFinanceExpense, 2) }}</td>
                                    <td class="text-end npv-col-ifrs16">-</td>
                                    <td class="text-end num-cell text-warning fw-bold npv-col-ifrs16">৳
                                        {{ number_format($initialResult->totalDepreciation, 2) }}</td>
                                    <td class="text-end num-cell fw-bold npv-col-ifrs16">৳ 0.00</td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Visual Trend Report Section -->
            <div class="npv-report-section">
                <div class="npv-section-header">
                    <h3 class="npv-section-title">
                        <i class="fa fa-chart-line text-info"></i> Cash Flow & Present Value Trend Visualization
                    </h3>
                </div>
                <div class="npv-chart-container">
                    <div style="height: 300px;">
                        <canvas id="npvChart"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-fixedcolumns/dataTables.fixedColumns.min.js') }}"></script>

    <script>
        One.helpersOnLoad(['jq-select2']);

        $(document).ready(function() {
            // DataTables initialization
            if ($('#npvBreakdownTable').length) {
                // Office / DG / Parking / Store Gross columns - hidden by default
                // to reduce clutter; toggled on via the "Show Space-wise Rent
                // Breakdown" switch above the table.
                var npvBreakdownColumns = [2, 3, 4, 5];
                var npvIfrs16Columns = {!! json_encode($initialResult && $initialResult->hasIfrs16Schedule ? [13, 14, 15, 16, 17, 18, 19] : []) !!};

                var npvTable = $('#npvBreakdownTable').DataTable({
                    pageLength: -1,
                    lengthMenu: [
                        [12, 25, 50, 100, -1],
                        [12, 25, 50, 100, "All"]
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    responsive: false,
                    scrollX: true,
                    scrollY: '55vh',
                    scrollCollapse: true,
                    autoWidth: false,
                    fixedColumns: {
                        left: 2
                    },
                    columnDefs: [{
                        targets: npvBreakdownColumns,
                        visible: false
                    }],
                    language: {
                        searchPlaceholder: "Search month or value..."
                    }
                });

                // Show/hide the space-wise rent breakdown columns on demand
                $('#npvToggleBreakdown').on('change', function() {
                    var showBreakdown = $(this).is(':checked');
                    npvBreakdownColumns.forEach(function(colIdx) {
                        npvTable.column(colIdx).visible(showBreakdown);
                    });
                    npvTable.columns.adjust();
                    if (npvTable.fixedColumns) {
                        npvTable.fixedColumns().relayout();
                    }
                });

                // Show/hide the IFRS 16 schedule columns on demand
                $('#npvToggleIfrs16').on('change', function() {
                    var showIfrs16 = $(this).is(':checked');
                    npvIfrs16Columns.forEach(function(colIdx) {
                        npvTable.column(colIdx).visible(showIfrs16);
                    });
                    npvTable.columns.adjust();
                    if (npvTable.fixedColumns) {
                        npvTable.fixedColumns().relayout();
                    }
                });
            }

            // Auto-set Calculation Base Date & Interest Rate when agreement is selected
            $('#agreement_id').on('change select2:select', function() {
                const selectedOption = $(this).find('option:selected');
                const paymentStart = selectedOption.data('payment-start') || selectedOption.data('from');
                const hasRate = selectedOption.data('has-rate');
                const interestRate = selectedOption.data('interest-rate');
                const refText = selectedOption.text().split('|')[0].trim();

                if (paymentStart) {
                    $('#base_date').val(paymentStart);
                }

                if (hasRate === 1 || hasRate === '1') {
                    $('#annual_discount_rate').val(interestRate).prop('disabled', false).attr('placeholder',
                        'e.g. 12.00');
                    $('#resetRateBtn').prop('disabled', false);
                    $('#calculateNpvBtn').prop('disabled', false);
                    $('#dynamicNoRateAlert').addClass('d-none');
                    $('#rateHelperText').html(
                        '<span class="text-success"><i class="fa fa-check-circle me-1"></i> Agreement rate: ' +
                        parseFloat(interestRate).toFixed(2) + '%</span>');
                } else if ($(this).val()) {
                    $('#annual_discount_rate').val('').prop('disabled', true).attr('placeholder',
                        'Not configured for agreement');
                    $('#resetRateBtn').prop('disabled', true);
                    $('#calculateNpvBtn').prop('disabled', true);
                    $('#noRateAlertMsg').html('Agreement <strong>' + refText +
                        '</strong> does not have an NPV interest rate configured. NPV values cannot be calculated.'
                    );
                    $('#dynamicNoRateAlert').removeClass('d-none');
                    $('#rateHelperText').html(
                        '<span class="text-danger"><i class="fa fa-exclamation-triangle me-1"></i> No interest rate set for this agreement.</span>'
                    );
                } else {
                    $('#annual_discount_rate').val('').prop('disabled', false).attr('placeholder',
                        'e.g. 12.00');
                    $('#resetRateBtn').prop('disabled', false);
                    $('#calculateNpvBtn').prop('disabled', false);
                    $('#dynamicNoRateAlert').addClass('d-none');
                    $('#rateHelperText').html(
                        '<span class="text-muted">Agreement-specific NPV rate.</span>');
                }
            });

            // Reset rate button
            $('#resetRateBtn').on('click', function() {
                const selectedOption = $('#agreement_id').find('option:selected');
                const interestRate = selectedOption.data('interest-rate');
                if (interestRate) {
                    $('#annual_discount_rate').val(interestRate);
                } else {
                    $('#annual_discount_rate').val('{{ $defaultRate }}');
                }
            });

            // Initialize Chart if results are present
            @if ($initialResult)
                const labels = {!! json_encode(array_column($initialResult->toArray()['cash_flows'], 'month_label')) !!};
                const netOutflows = {!! json_encode(array_column($initialResult->toArray()['cash_flows'], 'net_outflow')) !!};
                const presentValues = {!! json_encode(array_column($initialResult->toArray()['cash_flows'], 'present_value')) !!};
                const cumulativePVs = {!! json_encode(array_column($initialResult->toArray()['cash_flows'], 'cumulative_pv')) !!};

                const ctx = document.getElementById('npvChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Net Cash Outflow (Tk)',
                                data: netOutflows,
                                backgroundColor: 'rgba(148, 163, 184, 0.5)',
                                borderColor: '#94a3b8',
                                borderWidth: 1,
                                order: 2
                            },
                            {
                                label: 'Monthly Present Value (PV)',
                                data: presentValues,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: '#2563eb',
                                borderWidth: 1,
                                order: 1
                            },
                            {
                                label: 'Cumulative PV (Line)',
                                data: cumulativePVs,
                                type: 'line',
                                borderColor: '#0284c7',
                                backgroundColor: 'rgba(2, 132, 199, 0.1)',
                                borderWidth: 2.5,
                                pointRadius: 2,
                                fill: false,
                                yAxisID: 'y1',
                                order: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Monthly Amount (Tk)'
                                }
                            },
                            y1: {
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Cumulative PV (Tk)'
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endsection
