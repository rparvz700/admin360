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
    <style>
        .npv-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            padding: 1.75rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .npv-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }
        .npv-eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            font-weight: 600;
            color: #38bdf8;
            margin-bottom: 0.25rem;
        }
        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        .kpi-card .kpi-title {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.5rem;
        }
        .kpi-card .kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }
        .kpi-card .kpi-subtext {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }
        .bg-npv-primary {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        .bg-npv-success {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        .bg-npv-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
        .bg-npv-info {
            background-color: #f0f9ff;
            border-left: 4px solid #06b6d4;
        }
        /* High-specificity overrides for Bootstrap 5 & DataTables */
        #npvBreakdownTable,
        table.dataTable#npvBreakdownTable {
            font-size: 11px !important;
        }

        #npvBreakdownTable > thead > tr > th,
        table.dataTable#npvBreakdownTable > thead > tr > th,
        table.dataTable#npvBreakdownTable thead th {
            background-color: #f1f5f9 !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.02em !important;
            color: #334155 !important;
            white-space: nowrap !important;
            padding: 5px 8px !important;
            vertical-align: middle !important;
            line-height: 1.2 !important;
        }

        #npvBreakdownTable > tbody > tr > td,
        table.dataTable#npvBreakdownTable > tbody > tr > td,
        table.dataTable#npvBreakdownTable tbody td {
            font-size: 11px !important;
            padding: 4px 8px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            line-height: 1.25 !important;
        }

        #npvBreakdownTable .num-cell,
        table.dataTable#npvBreakdownTable .num-cell {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-size: 11px !important;
        }

        #npvBreakdownTable > tfoot > tr > td,
        table.dataTable#npvBreakdownTable > tfoot > tr > td {
            font-size: 11px !important;
            padding: 5px 8px !important;
            white-space: nowrap !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 11px !important;
        }

        .dataTables_wrapper input[type="search"],
        .dataTables_wrapper select {
            font-size: 11px !important;
            padding: 3px 8px !important;
        }

        /* Fixed First 2 and Last 2 Columns Styles */
        .npv-table-wrapper {
            overflow-x: auto;
            position: relative;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #ffffff;
        }

        .npv-col-left-1 {
            position: sticky !important;
            left: 0 !important;
            z-index: 5;
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
        }

        .npv-col-left-2 {
            position: sticky !important;
            left: 40px !important;
            z-index: 5;
            min-width: 110px !important;
            box-shadow: 3px 0 5px -2px rgba(0,0,0,0.12);
        }

        .npv-col-right-2 {
            position: sticky !important;
            right: 120px !important;
            z-index: 5;
            min-width: 135px !important;
            box-shadow: -3px 0 5px -2px rgba(0,0,0,0.12);
        }

        .npv-col-right-1 {
            position: sticky !important;
            right: 0 !important;
            z-index: 5;
            min-width: 120px !important;
        }

        /* Solid Background Colors for Sticky Cells */
        #npvBreakdownTable thead th.npv-col-left-1,
        #npvBreakdownTable thead th.npv-col-left-2,
        #npvBreakdownTable thead th.npv-col-right-2,
        #npvBreakdownTable thead th.npv-col-right-1 {
            background-color: #f1f5f9 !important;
            z-index: 8 !important;
        }

        #npvBreakdownTable tbody tr:nth-of-type(odd) td.npv-col-left-1,
        #npvBreakdownTable tbody tr:nth-of-type(odd) td.npv-col-left-2,
        #npvBreakdownTable tbody tr:nth-of-type(odd) td.npv-col-right-2,
        #npvBreakdownTable tbody tr:nth-of-type(odd) td.npv-col-right-1 {
            background-color: #ffffff !important;
        }

        #npvBreakdownTable tbody tr:nth-of-type(even) td.npv-col-left-1,
        #npvBreakdownTable tbody tr:nth-of-type(even) td.npv-col-left-2,
        #npvBreakdownTable tbody tr:nth-of-type(even) td.npv-col-right-2,
        #npvBreakdownTable tbody tr:nth-of-type(even) td.npv-col-right-1 {
            background-color: #f8fafc !important;
        }

        #npvBreakdownTable tbody tr:hover td.npv-col-left-1,
        #npvBreakdownTable tbody tr:hover td.npv-col-left-2,
        #npvBreakdownTable tbody tr:hover td.npv-col-right-2,
        #npvBreakdownTable tbody tr:hover td.npv-col-right-1 {
            background-color: #e2e8f0 !important;
        }

        #npvBreakdownTable tfoot td.npv-col-left-1,
        #npvBreakdownTable tfoot td.npv-col-left-2,
        #npvBreakdownTable tfoot td.npv-col-right-2,
        #npvBreakdownTable tfoot td.npv-col-right-1 {
            background-color: #0f172a !important;
            color: #ffffff !important;
            z-index: 8 !important;
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <!-- Header -->
        <div class="npv-header shadow-sm">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="npv-eyebrow"><i class="fa fa-calculator me-1"></i> Facilities Management</div>
                    <h2>Net Present Value (NPV) Calculation</h2>
                    <p class="text-slate-300 mb-0 fs-sm">Calculate projected lease cash outflows, compounded rent escalations, advance deductions, and present values.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.finance-settings.index') }}" class="btn btn-outline-light btn-sm" title="Configure Default Annual Interest Rate">
                        <i class="fa fa-cog me-1"></i> Rate Settings
                    </a>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter & Control Form -->
        <div class="block block-rounded border shadow-sm mb-4">
            <div class="block-header block-header-default bg-light">
                <h3 class="block-title fs-sm font-semibold text-uppercase text-muted"><i class="fa fa-filter me-1"></i> Calculation Inputs</h3>
            </div>
            <div class="block-content">
                <form action="{{ route('facilities.npv.calculate') }}" method="POST" id="npvForm">
                    @csrf
                    <div class="row g-3 mb-3">
                        <!-- Select Agreement -->
                        <div class="col-md-5">
                            <label class="form-label font-semibold fs-sm">Select Property Lease Agreement <span class="text-danger">*</span></label>
                            <select name="agreement_id" id="agreement_id" class="form-select js-select2" data-placeholder="-- Choose Agreement --" style="width: 100%;" required>
                                <option value=""></option>
                                @foreach ($agreements as $agr)
                                    @php
                                        $bName = $agr->floors->first()->building->site_name ?? ($agr->floors->first()->building->code ?? 'N/A');
                                        $vendorName = $agr->vendor->name ?? 'N/A';
                                    @endphp
                                    <option value="{{ $agr->id }}" 
                                        {{ (old('agreement_id', $selectedAgreementId) == $agr->id) ? 'selected' : '' }}
                                        data-from="{{ $agr->from_date }}"
                                        data-to="{{ $agr->to_date }}">
                                        {{ $agr->agreement_ref_no }} | Site: {{ $bName }} ({{ $vendorName }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Base Date -->
                        <div class="col-md-3">
                            <label class="form-label font-semibold fs-sm">Calculation Base Date <span class="text-danger">*</span></label>
                            <input type="date" name="base_date" id="base_date" class="form-control"
                                value="{{ old('base_date', $defaultBaseDate) }}" required>
                            <div class="form-text fs-xs">Defaults to Agreement's <strong>From Date</strong> (Lease Start Date)</div>
                        </div>

                        <!-- Annual Discount Rate -->
                        <div class="col-md-4">
                            <label class="form-label font-semibold fs-sm">
                                Annual Discount / Interest Rate (%) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.0001" min="0" max="100" name="annual_discount_rate" id="annual_discount_rate" class="form-control"
                                    value="{{ old('annual_discount_rate', request('annual_discount_rate', $defaultRate)) }}" required>
                                <span class="input-group-text">%</span>
                                <button type="button" class="btn btn-outline-secondary" id="resetRateBtn" title="Reset to DB Default ({{ $defaultRate }}%)">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text fs-xs">Default sourced from DB: <strong>{{ $defaultRate }}%</strong></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                        <span class="fs-xs text-muted">
                            <i class="fa fa-info-circle me-1"></i> Tax top-up (gross-up) & VAT are automatically read from the agreement's <code>rent_base</code> table in DB.
                        </span>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-calculator me-1"></i> Calculate NPV
                            </button>
                            @if ($initialResult)
                                <a href="{{ route('facilities.npv.export', ['agreement_id' => $initialResult->agreement->id, 'base_date' => $initialResult->baseDate, 'annual_discount_rate' => $initialResult->annualDiscountRate]) }}" 
                                   class="btn btn-outline-success">
                                    <i class="fa fa-file-excel me-1"></i> Export CSV
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($initialResult)
            <!-- Database Source Data Inspection -->
            <div class="block block-rounded border shadow-sm mb-4">
                <div class="block-header block-header-default bg-light">
                    <h3 class="block-title fs-xs font-semibold text-uppercase text-muted">
                        <i class="fa fa-database me-1"></i> Database Rent Source Data (Agreement #{{ $initialResult->agreement->id }} - {{ $initialResult->agreement->agreement_ref_no }})
                    </h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
                    </div>
                </div>
                <div class="block-content py-3">
                    @php 
                        $latestRentBase = $initialResult->agreement->rentBases->sortByDesc('id')->first();
                        $latestSd = $initialResult->agreement->securityDeposits->sortByDesc('id')->first();
                    @endphp
                    <div class="row g-3 fs-xs">
                        <div class="col-md-3">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted font-semibold">Rent Base (Latest DB Entry)</div>
                                <div>Base Rent: <strong>৳ {{ number_format($latestRentBase->base_rent ?? 0, 2) }}</strong></div>
                                <div>At Source Tax: <strong>{{ ($latestRentBase->is_at_source ?? false) ? 'YES (1)' : 'NO (0)' }}</strong></div>
                                <div>VAT: ৳ {{ number_format($latestRentBase->vat ?? 0, 2) }} | Tax: ৳ {{ number_format($latestRentBase->tax ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted font-semibold mb-1">Rent Components (Latest Entry)</div>
                                @if ($latestRentBase && $latestRentBase->components->count() > 0)
                                    <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 11px;">
                                        <thead>
                                            <tr class="table-secondary">
                                                <th>Type</th>
                                                <th class="text-end">Area</th>
                                                <th class="text-end">Rent Amount</th>
                                                <th class="text-end">Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($latestRentBase->components as $comp)
                                                <tr>
                                                    <td><strong>{{ $comp->component_type }}</strong></td>
                                                    <td class="text-end">{{ number_format($comp->area_sft, 2) }}</td>
                                                    <td class="text-end">৳ {{ number_format($comp->rent_amount, 2) }}</td>
                                                    <td class="text-end">৳ {{ number_format($comp->total_amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <em class="text-muted">No component breakdown rows. Using base_rent fallback: ৳ {{ number_format($latestRentBase->base_rent ?? 0, 2) }}</em>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted font-semibold">Security Deposit & Advance (Latest Entry)</div>
                                <div>Total Deposit: <strong>৳ {{ number_format($latestSd->security_deposit_total ?? 0, 2) }}</strong></div>
                                <div>Adjustable Advance: <strong>৳ {{ number_format($latestSd->security_deposit_absorbable ?? 0, 2) }}</strong></div>
                                <div>Non-Adjustable Deposit: <strong>৳ {{ number_format($latestSd->security_deposit_non_absorbable ?? 0, 2) }}</strong></div>
                                <div class="mt-1 fs-2xs text-muted">
                                    Interval: {{ $latestSd->absorb_frequency ?? 'N/A' }} months | Start: {{ $latestSd->absorb_start_date ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="kpi-card bg-npv-primary">
                        <div class="kpi-title"><i class="fa fa-coins me-1 text-primary"></i> Total NPV (Present Value)</div>
                        <div class="kpi-value text-primary">৳ {{ number_format($initialResult->totalNPV, 2) }}</div>
                        <div class="kpi-subtext">Sum of discounted monthly net outflows</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card bg-npv-warning">
                        <div class="kpi-title"><i class="fa fa-money-bill-wave me-1 text-warning"></i> Total Undiscounted Outflow</div>
                        <div class="kpi-value text-warning">৳ {{ number_format($initialResult->totalUndiscountedOutflow, 2) }}</div>
                        <div class="kpi-subtext">Nominal total cash outflow</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card bg-npv-info">
                        <div class="kpi-title"><i class="fa fa-calendar-alt me-1 text-info"></i> Lease Duration & Rate</div>
                        <div class="kpi-value text-info">{{ $initialResult->totalMonths }} <span class="fs-xs font-normal">months</span></div>
                        <div class="kpi-subtext">Monthly Rate: {{ number_format($initialResult->monthlyDiscountRate * 100, 4) }}%</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card bg-npv-success">
                        <div class="kpi-title"><i class="fa fa-hand-holding-usd me-1 text-success"></i> Advance (Adjustable)</div>
                        <div class="kpi-value text-success">৳ {{ number_format($initialResult->absorbableAdvanceTotal, 2) }}</div>
                        <div class="kpi-subtext">Deducted in window: ৳ {{ number_format($initialResult->totalAdvanceDeductions, 2) }} ({{ $initialResult->totalMonths }} mos) | SD: ৳ {{ number_format($initialResult->nonAbsorbableDepositTotal, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="block block-rounded border shadow-sm mb-4">
                <div class="block-header block-header-default">
                    <h3 class="block-title fs-sm font-semibold text-uppercase text-muted"><i class="fa fa-chart-line me-1"></i> Cash Flow & Present Value Trend</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
                    </div>
                </div>
                <div class="block-content block-content-full">
                    <div style="height: 320px;">
                        <canvas id="npvChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Monthly Breakdown Table -->
            <div class="block block-rounded border shadow-sm mb-4">
                <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                    <h3 class="block-title fs-xs font-semibold text-uppercase text-muted">
                        <i class="fa fa-table me-1"></i> Monthly Cash Outflow & PV Breakdown ({{ $initialResult->totalMonths }} Months)
                    </h3>
                    <a href="{{ route('facilities.npv.export', ['agreement_id' => $initialResult->agreement->id, 'base_date' => $initialResult->baseDate, 'annual_discount_rate' => $initialResult->annualDiscountRate]) }}" 
                       class="btn btn-sm btn-alt-success">
                        <i class="fa fa-download me-1"></i> Export Schedule
                    </a>
                </div>
                <div class="block-content block-content-full">
                    <div class="npv-table-wrapper">
                        <table class="table table-sm table-bordered table-striped table-hover table-vcenter table-npv" id="npvBreakdownTable">
                            <thead>
                                <tr>
                                    <th class="text-center npv-col-left-1">#</th>
                                    <th class="text-center npv-col-left-2">Billing Month</th>
                                    <th class="text-end">Office Gross (৳)</th>
                                    <th class="text-end">DG Gross (৳)</th>
                                    <th class="text-end">Parking Gross (৳)</th>
                                    <th class="text-end">Store Gross (৳)</th>
                                    <th class="text-end">Total Gross (৳)</th>
                                    <th class="text-end text-danger">Advance Adj. (-৳)</th>
                                    <th class="text-end text-success">SD Refund (-৳)</th>
                                    <th class="text-end fw-bold">Net Outflow (৳)</th>
                                    <th class="text-center">Discount Factor</th>
                                    <th class="text-end text-primary fw-bold npv-col-right-2">Present Value (NPV) (৳)</th>
                                    <th class="text-end text-info npv-col-right-1">Cumulative PV (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($initialResult->cashFlows as $cf)
                                    <tr>
                                        <td class="text-center text-muted font-mono fs-xs npv-col-left-1">{{ $cf->periodIndex }}</td>
                                        <td class="text-center fw-semibold fs-xs npv-col-left-2">
                                            {{ $cf->monthLabel }}
                                            @if (!empty($cf->activeIncrements))
                                                <span class="badge bg-warning-light text-warning me-1" title="Rent Increment Active">
                                                    <i class="fa fa-arrow-up"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end num-cell">{{ number_format($cf->officeGrossRent, 2) }}</td>
                                        <td class="text-end num-cell">{{ number_format($cf->dgGrossRent, 2) }}</td>
                                        <td class="text-end num-cell">{{ number_format($cf->parkingGrossRent, 2) }}</td>
                                        <td class="text-end num-cell">{{ number_format($cf->storeGrossRent, 2) }}</td>
                                        <td class="text-end num-cell fw-semibold">{{ number_format($cf->totalGrossRent, 2) }}</td>
                                        <td class="text-end num-cell text-danger">
                                            {{ $cf->advanceDeduction > 0 ? '-' . number_format($cf->advanceDeduction, 2) : '0.00' }}
                                        </td>
                                        <td class="text-end num-cell text-success">
                                            {{ $cf->depositRefund > 0 ? '-' . number_format($cf->depositRefund, 2) : '0.00' }}
                                        </td>
                                        <td class="text-end num-cell fw-bold">{{ number_format($cf->netOutflow, 2) }}</td>
                                        <td class="text-center num-cell text-muted fs-xs">{{ number_format($cf->discountFactor, 6) }}</td>
                                        <td class="text-end num-cell text-primary fw-bold npv-col-right-2">৳ {{ number_format($cf->presentValue, 2) }}</td>
                                        <td class="text-end num-cell text-info fs-xs npv-col-right-1">৳ {{ number_format($cf->cumulativePV, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <td class="text-center npv-col-left-1"></td>
                                    <td class="text-end fw-bold npv-col-left-2">TOTALS:</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end num-cell fw-bold">৳ {{ number_format($initialResult->totalGrossRent, 2) }}</td>
                                    <td class="text-end num-cell text-warning fw-bold">-৳ {{ number_format($initialResult->totalAdvanceDeductions, 2) }}</td>
                                    <td class="text-end num-cell text-success fw-bold">-৳ {{ number_format($initialResult->totalDepositRefunds, 2) }}</td>
                                    <td class="text-end num-cell fw-bold">৳ {{ number_format($initialResult->totalUndiscountedOutflow, 2) }}</td>
                                    <td class="text-center">-</td>
                                    <td class="text-end num-cell text-white fw-bold fs-xs npv-col-right-2">৳ {{ number_format($initialResult->totalNPV, 2) }}</td>
                                    <td class="text-end npv-col-right-1">-</td>
                                </tr>
                            </tfoot>
                        </table>
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

    <script>
        One.helpersOnLoad(['jq-select2']);

        $(document).ready(function() {
            // DataTables initialization
            if ($('#npvBreakdownTable').length) {
                $('#npvBreakdownTable').DataTable({
                    pageLength: 25,
                    lengthMenu: [[12, 25, 50, 100, -1], [12, 25, 50, 100, "All"]],
                    order: [[0, 'asc']],
                    responsive: false,
                    scrollX: true,
                    autoWidth: false,
                    language: {
                        searchPlaceholder: "Search month or value..."
                    }
                });
            }

            // Auto-set Calculation Base Date to Agreement's From Date when agreement is selected
            $('#agreement_id').on('change select2:select', function() {
                const selectedOption = $(this).find('option:selected');
                const fromDate = selectedOption.data('from');
                if (fromDate) {
                    $('#base_date').val(fromDate);
                }
            });

            // Reset rate button
            $('#resetRateBtn').on('click', function() {
                $('#annual_discount_rate').val('{{ $defaultRate }}');
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
                        datasets: [
                            {
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
