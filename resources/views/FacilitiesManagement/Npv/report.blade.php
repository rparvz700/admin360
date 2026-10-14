@extends('Partials.app', ['activeMenu' => 'facilities.npv.report'])

@section('title')
    Net Present Value (NPV) Valuation Report - {{ config('app.name') }}
@endsection

@section('page_title')
    Net Present Value (NPV) Valuation Report
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/npv-calculation.css') }}">
@endsection

@section('content')
    <div class="content">
        <!-- Report Header -->
        <div class="npv-report-header d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <div class="npv-report-eyebrow">
                    <i class="fa fa-chart-bar me-1"></i> Facilities Management &bull; Financial Analytics
                </div>
                <h2 class="npv-report-title">Net Present Value (NPV) Valuation Report</h2>
                <p class="npv-report-sub">
                    Aggregated valuation, variable agreement interest rates, and discounted present values across active
                    lease agreements.
                </p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('facilities.npv.report.refresh-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                        title="Recalculate and flush cached report metrics">
                        <i class="fa fa-sync-alt me-1"></i> Refresh Data
                    </button>
                </form>
                {{-- <a href="{{ route('facilities.npv.index') }}" class="btn btn-alt-primary btn-sm">
                    <i class="fa fa-calculator me-1"></i> Calculation Workbench
                </a> --}}
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                <i class="fa fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Executive Summary Strip (Single Bar Layout matching Workbench) -->
        <div class="npv-summary-strip">
            <div class="row g-0">
                <div class="col-6 col-lg-3 summary-metric-item hero-metric ps-4 ps-md-5"
                    title="Total Portfolio NPV: Sum of discounted monthly net cash outflows across agreements with configured interest rates">
                    <div class="metric-label" title="Total Portfolio NPV"><i class="fa fa-coins me-1 text-primary"></i>
                        Total Portfolio NPV</div>
                    <div class="metric-value text-npv-primary" id="kpiPortfolioTotalNPV"
                        title="৳ {{ number_format($portfolioTotalNPV, 2) }}">৳
                        {{ number_format($portfolioTotalNPV, 2) }}</div>
                    <div class="metric-subtext" id="kpiCalculatedSubtext"
                        title="Discounted Present Value Sum across agreements with configured rate">
                        {{ $calculatedCount }} of {{ $totalAgreements }} leases calculated</div>
                </div>
                <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                    title="Total Nominal Outflow: Undiscounted total cash outflow across all active agreements">
                    <div class="metric-label" title="Total Nominal Outflow"><i
                            class="fa fa-money-bill-wave me-1 text-warning"></i> Total Nominal Outflow</div>
                    <div class="metric-value text-npv-warning" id="kpiPortfolioTotalOutflow"
                        title="৳ {{ number_format($portfolioTotalOutflow, 2) }}">৳
                        {{ number_format($portfolioTotalOutflow, 2) }}</div>
                    <div class="metric-subtext" title="Undiscounted gross cash outflow total across all active agreements">
                        Undiscounted Cash Total</div>
                </div>
                <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                    title="Active Agreements: {{ $totalAgreements }} active agreements ({{ $calculatedCount }} with rate configured)">
                    <div class="metric-label" title="Active Agreements"><i class="fa fa-file-contract me-1 text-info"></i>
                        Active Agreements</div>
                    <div class="metric-value text-npv-info" title="{{ $totalAgreements }} active leases">
                        {{ $totalAgreements }} <span class="fs-xs font-normal">leases</span></div>
                    <div class="metric-subtext" id="kpiLeasesSubtext" title="Configured interest rate count">
                        {{ $calculatedCount }} with rate ({{ $totalAgreements - $calculatedCount }} pending)</div>
                </div>
                <div class="col-6 col-lg-3 summary-metric-item ps-4 ps-md-5"
                    title="Average Lease NPV: Portfolio average Net Present Value per calculated agreement">
                    <div class="metric-label" title="Average Lease NPV"><i class="fa fa-chart-line me-1 text-success"></i>
                        Average Lease NPV</div>
                    <div class="metric-value text-npv-success" id="kpiAverageNPV"
                        title="৳ {{ number_format($averageNPV, 2) }}">৳
                        {{ number_format($averageNPV, 2) }}</div>
                    <div class="metric-subtext" title="Per Agreement Average NPV (for agreements with rate)">
                        Per Agreement Average</div>
                </div>
            </div>
        </div>

        <!-- Portfolio Table Section -->
        <div class="block block-rounded border">
            <div class="block-header block-header-default d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <h3 class="block-title fs-sm font-semibold mb-0">
                        <i class="fa fa-table me-1 text-primary"></i> Agreement NPV Summary Listing
                    </h3>
                    <div class="fs-2xs text-muted">Sorted by Total NPV descending by default. Click "Set Rate" to configure
                        interest rate for any agreement.</div>
                </div>
            </div>
            <div class="block-content block-content-full p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter table-hover w-100 table-npv-summary"
                        id="npvSummaryTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 35px;">#</th>
                                <th>Agreement Ref</th>
                                <th class="d-none">Site / Building</th>
                                <th>Vendor</th>
                                <th class="d-none">Payment Start Date</th>
                                <th class="d-none">Expiry Date</th>
                                <th class="text-center" style="width: 140px;">Lease Period</th>
                                <th class="text-center" style="width: 60px;">Months</th>
                                <th class="text-end" style="width: 140px;">Total Gross Rent (৳)</th>
                                <th class="text-center" style="width: 130px;">Interest Rate (%)</th>
                                <th class="text-end text-primary font-bold" style="width: 160px;">Total NPV Value (৳)</th>
                                <th class="text-end" style="width: 140px;">Nominal Outflow (৳)</th>
                                <th class="text-center" style="width: 90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summaryRows as $index => $row)
                                @php
                                    $pStart = $row->paymentStartDate ?? ($row->fromDate ?? null);
                                    $pExpiry = $row->expiryDate ?? ($row->toDate ?? null);
                                    $formattedFrom =
                                        $pStart && $pStart !== 'N/A'
                                            ? \Carbon\Carbon::parse($pStart)->format('d M Y')
                                            : 'N/A';
                                    $formattedTo =
                                        $pExpiry && $pExpiry !== 'N/A'
                                            ? \Carbon\Carbon::parse($pExpiry)->format('d M Y')
                                            : 'N/A';
                                @endphp
                                <tr id="row-agreement-{{ $row->agreementId }}">
                                    <td class="text-center text-muted npv-num-cell fs-2xs">{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('facilities.npv.index', ['agreement_id' => $row->agreementId]) }}"
                                                class="npv-pill-ref" title="View in calculation workbench">
                                                {{ $row->agreementRefNo }}
                                            </a>
                                        </div>
                                        <div class="fs-3xs text-muted font-medium mt-1">
                                            <i
                                                class="fa fa-building text-secondary me-1 opacity-75"></i>{{ $row->siteName }}
                                        </div>
                                    </td>
                                    <td class="d-none">{{ $row->siteName }}</td>
                                    <td class="font-medium">{{ $row->vendorName }}</td>
                                    <td class="d-none">{{ $formattedFrom }}</td>
                                    <td class="d-none">{{ $formattedTo }}</td>
                                    <td class="text-center fs-2xs text-muted" style="line-height: 1.3;">
                                        <div class="fw-semibold text-dark">{{ $formattedFrom }}</div>
                                        <div class="fs-3xs text-muted opacity-75">{{ $formattedTo }}</div>
                                    </td>
                                    <td class="text-center npv-num-cell">{{ $row->totalMonths }}</td>
                                    <td class="text-end npv-num-cell">{{ number_format($row->totalGrossRent, 2) }}</td>
                                    <td class="text-center rate-cell"
                                        data-order="{{ $row->hasInterestRate ? $row->annualDiscountRate : -1 }}">
                                        @if ($row->hasInterestRate && $row->annualDiscountRate !== null)
                                            <span
                                                class="badge bg-primary-light text-white fw-semibold fs-2xs px-2 py-1 rate-display-badge">
                                                {{ number_format($row->annualDiscountRate, 2) }}%
                                            </span>
                                            <button type="button"
                                                class="btn btn-xs btn-link text-primary p-0 ms-1 set-rate-trigger-btn"
                                                data-id="{{ $row->agreementId }}" data-ref="{{ $row->agreementRefNo }}"
                                                data-current-rate="{{ $row->annualDiscountRate }}"
                                                title="Edit NPV interest rate">
                                                <i class="fa fa-pen fs-3xs"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-xs btn-outline-warning text-dark py-0 px-2 set-rate-trigger-btn"
                                                data-id="{{ $row->agreementId }}" data-ref="{{ $row->agreementRefNo }}"
                                                data-current-rate="" title="Set NPV interest rate for this agreement">
                                                <i class="fa fa-plus me-1"></i>Set Rate
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-end npv-cell"
                                        data-order="{{ $row->hasInterestRate && $row->totalNPV !== null ? $row->totalNPV : -1 }}">
                                        @if ($row->hasInterestRate && $row->totalNPV !== null)
                                            <span class="npv-value-cell font-bold text-primary">৳
                                                {{ number_format($row->totalNPV, 2) }}</span>
                                        @else
                                            <span class="badge bg-light text-muted border fst-italic fs-3xs">Rate not
                                                set</span>
                                        @endif
                                    </td>
                                    <td class="text-end npv-num-cell text-muted"
                                        data-order="{{ $row->totalUndiscountedOutflow }}">
                                        ৳ {{ number_format($row->totalUndiscountedOutflow, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{--
                                            @if ($row->hasInterestRate && $row->totalNPV !== null)
                                                <button type="button" class="btn btn-xs btn-alt-primary view-detail-btn px-2 py-1"
                                                    data-id="{{ $row->agreementId }}"
                                                    data-ref="{{ $row->agreementRefNo }}"
                                                    data-vendor="{{ $row->vendorName }}"
                                                    data-site="{{ $row->siteName }}"
                                                    title="View detailed breakdown">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            @endif
                                            --}}
                                            <a href="{{ route('facilities.npv.index', ['agreement_id' => $row->agreementId]) }}"
                                                class="btn btn-xs btn-alt-secondary px-2 py-1"
                                                title="Open in Calculation Workbench">
                                                <i class="fa fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-end font-bold text-muted fs-2xs">PORTFOLIO TOTALS:</td>
                                <td class="text-end npv-num-cell font-bold">৳
                                    {{ number_format($portfolioTotalGrossRent, 2) }}</td>
                                <td class="text-center font-bold text-muted fs-3xs">-</td>
                                <td class="text-end npv-num-cell text-primary font-bold" id="footPortfolioTotalNPV">৳
                                    {{ number_format($portfolioTotalNPV, 2) }}</td>
                                <td class="text-end npv-num-cell text-muted font-bold" id="footPortfolioTotalOutflow">৳
                                    {{ number_format($portfolioTotalOutflow, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Detail Modal Partial -->
    @include('FacilitiesManagement.Npv.partials.detail_modal')

    <!-- Set NPV Interest Rate Modal -->
    <div class="modal fade" id="setInterestRateModal" tabindex="-1" aria-labelledby="setInterestRateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="setInterestRateForm">
                    @csrf
                    <div class="modal-header bg-body-light py-2">
                        <h5 class="modal-title fs-sm font-semibold" id="setInterestRateModalLabel">
                            <i class="fa fa-percentage text-primary me-1"></i> Configure NPV Interest Rate
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="alert alert-info py-2 px-3 fs-xs mb-3">
                            <i class="fa fa-info-circle me-1"></i>
                            Configuring annual discount rate for: <strong id="modalSetRateRefNo"
                                class="text-dark"></strong>
                        </div>

                        <div id="setRateAlertBox" class="alert alert-danger d-none py-2 px-3 fs-xs mb-3"></div>

                        <input type="hidden" id="modalSetRateAgreementId" name="agreement_id" value="">

                        <div class="mb-3">
                            <label class="form-label fs-xs fw-semibold" for="modalInterestRateInput">
                                Annual Discount Rate (%) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" max="100"
                                    class="form-control form-control-sm" id="modalInterestRateInput" name="interest_rate"
                                    placeholder="e.g. 12.00">
                                <span class="input-group-text fs-xs">%</span>
                            </div>
                            <div class="form-text fs-3xs text-muted">
                                If cleared, NPV calculation is disabled for this agreement.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-body-light py-2 d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-sm btn-alt-danger" id="btnClearRateBtn">
                                <i class="fa fa-trash-alt me-1"></i> Clear Rate
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-alt-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="btnSaveInterestRate">
                                <i class="fa fa-save me-1"></i> Save & Recalculate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables with Default Sorting on Total NPV (Column 10) Descending
            var table = $('#npvSummaryTable').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                order: [
                    [10, 'desc']
                ], // Value-wise sorting by Total NPV Value (descending)
                responsive: true,
                autoWidth: false,
                columnDefs: [{
                        targets: [2, 4, 5],
                        visible: false
                    } // Hidden in UI, exported to CSV/Excel as separate columns
                ],
                dom: "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'d-flex justify-content-end gap-2'Bf>>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fa fa-file-excel me-1 text-success"></i> Export CSV',
                    className: 'btn btn-xs btn-alt-success',
                    title: 'NPV_Portfolio_Summary_Report_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 7, 8, 9, 10,
                            11
                        ], // Excludes UI-combined col 6 and action col 12
                        format: {
                            header: function(data, columnIdx) {
                                const customHeaders = {
                                    0: 'SL #',
                                    1: 'Agreement Ref No',
                                    2: 'Site / Building',
                                    3: 'Vendor / Landlord',
                                    4: 'Start Date',
                                    5: 'Expiry Date',
                                    7: 'Duration (Months)',
                                    8: 'Total Gross Rent',
                                    9: 'Interest Rate (%)',
                                    10: 'Total NPV Value',
                                    11: 'Nominal Outflow'
                                };
                                return customHeaders[columnIdx] || data.replace(/<[^>]+>/g, '')
                                    .replace(/৳/g, '').trim();
                            },
                            body: function(data, rowIdx, columnIdx, node) {
                                let text = $(node).text().replace(/৳\s?/g, '').trim();
                                if (columnIdx === 1) {
                                    return $(node).find('.npv-pill-ref').text().trim() || text
                                        .split('\n')[0].trim();
                                }
                                return text;
                            }
                        }
                    }
                }],
                language: {
                    searchPlaceholder: "Search ref, site, vendor..."
                }
            });

            // Ordinal suffix helper (1st, 2nd, 3rd...)
            function getOrdinal(n) {
                const s = ["th", "st", "nd", "rd"];
                const v = n % 100;
                return n + (s[(v - 20) % 10] || s[v] || s[0]);
            }

            // Date formatter helper (e.g. 05 Aug 2026)
            function formatDateStr(dateStr) {
                if (!dateStr || dateStr === 'N/A') return 'N/A';
                try {
                    const parts = dateStr.split('-');
                    if (parts.length === 3) {
                        const year = parseInt(parts[0], 10);
                        const month = parseInt(parts[1], 10) - 1;
                        const day = parseInt(parts[2], 10);
                        const dt = new Date(year, month, day);
                        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
                            "Dec"
                        ];
                        const dayStr = day < 10 ? '0' + day : day;
                        return `${dayStr} ${months[dt.getMonth()]} ${dt.getFullYear()}`;
                    }
                    return dateStr;
                } catch (e) {
                    return dateStr;
                }
            }

            // AJAX Detail Modal Handler
            const detailModal = new bootstrap.Modal(document.getElementById('npvDetailModal'));

            $(document).on('click', '.view-detail-btn', function() {
                const agrId = $(this).data('id');
                const refNo = $(this).data('ref');
                const vendor = $(this).data('vendor');
                const site = $(this).data('site');

                // Set header metadata
                $('#modalAgrRef').text(refNo);
                $('#modalVendorName').text(vendor);
                $('#modalSiteName').text(site);

                // Set link to full workbench page
                const workbenchUrl = "{{ route('facilities.npv.index') }}?agreement_id=" + agrId;
                $('#modalFullWorkbenchBtn').attr('href', workbenchUrl);

                // Show modal and reset UI state
                $('#modalLoadingSpinner').removeClass('d-none');
                $('#modalMainContent').addClass('d-none');
                $('#modalErrorAlert').addClass('d-none');
                $('#modalCashflowRows').empty();

                detailModal.show();

                // Fetch detail breakdown via AJAX (uses agreement's configured interest rate)
                $.ajax({
                    url: "/facilities/npv/report/" + agrId + "/detail",
                    type: "GET",
                    success: function(response) {
                        $('#modalLoadingSpinner').addClass('d-none');

                        if (response.success && response.data) {
                            const res = response.data;

                            // Fill summary strip metrics (matching Workbench format)
                            $('#modalTotalNPV').text('৳ ' + res.total_npv.toLocaleString(
                                'en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalTotalOutflow').text('৳ ' + res.total_undiscounted_outflow
                                .toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalTotalMonths').text(res.total_months);
                            $('#modalDateRange').text(formatDateStr(res.base_date) + ' to ' +
                                formatDateStr(res.expiry_date));
                            $('#modalMonthlyRate').text('Monthly: ' + res.monthly_discount_rate
                                .toFixed(4) + '% (Annual: ' + res.annual_discount_rate
                                .toFixed(2) + '%)');

                            // Populate Rent Base & Contract Source Audit section
                            if (res.audit) {
                                const audit = res.audit;
                                $('#modalAuditBaseRent').text(audit.base_rent.toLocaleString(
                                    'en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                $('#modalAuditTaxSource').text(audit.is_at_source ? 'YES (1)' :
                                    'NO (0)');
                                $('#modalAuditVat').text(audit.vat.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                                $('#modalAuditTax').text(audit.tax.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                                $('#modalAuditStartDate').text(formatDateStr(res.base_date));
                                $('#modalAuditEndDate').text(formatDateStr(res.expiry_date));

                                $('#modalAuditSdTotal').text(audit.sd_total.toLocaleString(
                                    'en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                $('#modalAuditSdAbsorbable').text(audit.sd_absorbable
                                    .toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                $('#modalAuditSdNonAbsorbable').text(audit.sd_non_absorbable
                                    .toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                $('#modalAuditSdFrequency').text(audit.sd_frequency || 'N/A');
                                $('#modalAuditSdStartDate').text(formatDateStr(audit
                                    .sd_start_date));

                                if (audit.sd_clauses && audit.sd_clauses.length > 0) {
                                    const activeClauses = audit.sd_clauses.filter(c => c
                                        .absorb_amount > 0 || c.frequency > 0);
                                    if (activeClauses.length > 1) {
                                        $('#modalAuditSdBadge').text(activeClauses.length +
                                            ' Clauses').removeClass('d-none');
                                    } else {
                                        $('#modalAuditSdBadge').addClass('d-none');
                                    }

                                    if (activeClauses.length > 0) {
                                        let sdHtml = `
                                            <div class="mt-2 border-top pt-2">
                                                <div class="fw-semibold text-dark mb-1 fs-3xs">Adjustment Clauses Schedule:</div>
                                                <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                                    <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 10px;">
                                                        <thead>
                                                            <tr class="table-secondary" style="font-size: 10px; text-transform: uppercase;">
                                                                <th>Clause</th>
                                                                <th class="text-end">Amount</th>
                                                                <th class="text-center">Interval</th>
                                                                <th class="text-end">Monthly</th>
                                                                <th class="text-center">Period</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>`;
                                        activeClauses.forEach(function(c, idx) {
                                            let amt = c.absorb_amount || 0;
                                            let freq = c.frequency || 0;
                                            let monthly = (amt > 0 && freq > 0) ? (amt /
                                                freq) : 0;
                                            let startStr = c.start_date ? formatDateStr(
                                                c.start_date) : 'Auto/Seq';
                                            let endStr = c.end_date ? formatDateStr(c
                                                .end_date) : (freq > 0 ? freq +
                                                ' mos' : 'N/A');

                                            sdHtml += `
                                                <tr>
                                                    <td>Clause #${idx + 1}</td>
                                                    <td class="text-end">৳ ${amt.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    <td class="text-center">${freq} mos</td>
                                                    <td class="text-end text-info fw-semibold">৳ ${monthly.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    <td class="text-center fs-3xs">${startStr} - ${endStr}</td>
                                                </tr>`;
                                        });
                                        sdHtml += `</tbody></table></div></div>`;
                                        $('#modalAuditSdClausesBox').html(sdHtml);
                                    }
                                }

                                // Components Breakdown Table
                                if (audit.components && audit.components.length > 0) {
                                    let compHtml = `
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 11px;">
                                                <thead>
                                                    <tr class="table-secondary" style="font-size: 12px; text-transform: uppercase;">
                                                        <th>Type</th>
                                                        <th class="text-end">Area</th>
                                                        <th class="text-end">Rent</th>
                                                        <th class="text-end">VAT</th>
                                                        <th class="text-end">TAX</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                                    audit.components.forEach(function(comp) {
                                        let vatVal = comp.vat !== undefined ? comp.vat :
                                            0;
                                        let taxVal = comp.tax !== undefined ? comp.tax :
                                            0;
                                        compHtml += `
                                            <tr>
                                                <td><strong>${comp.type}</strong></td>
                                                <td class="text-end">${comp.area.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                <td class="text-end">৳ ${comp.rent.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                <td class="text-end">৳ ${vatVal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                <td class="text-end">৳ ${taxVal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                <td class="text-end font-bold">৳ ${comp.total.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>`;
                                    });
                                    compHtml += `</tbody></table></div>`;
                                    $('#modalAuditComponentsBox').html(compHtml);
                                } else {
                                    $('#modalAuditComponentsBox').html(
                                        '<em class="text-muted">No component breakdown rows. Using base_rent fallback: ৳ ' +
                                        audit.base_rent.toLocaleString('en-US', {
                                            minimumFractionDigits: 2
                                        }) + '</em>');
                                }

                                // Rent Escalation Cycles Table
                                if (audit.increments && audit.increments.length > 0) {
                                    $('#modalAuditIncBadge').text(audit.increments.length +
                                        ' Defined');
                                    let incHtml = `
                                        <div class="table-responsive" style="max-height: 140px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 11px;">
                                                <thead>
                                                    <tr class="table-secondary" style="font-size: 12px; text-transform: uppercase;">
                                                        <th class="text-center" style="width: 45px;">Cycle</th>
                                                        <th class="text-center">Effective From</th>
                                                        <th class="text-end">Escalation</th>
                                                        <th class="text-end">New Rent</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                                    audit.increments.forEach(function(inc) {
                                        let ord = getOrdinal(inc.cycle);
                                        let escStr = inc.percentage !== null ? inc
                                            .percentage.toFixed(2) + '%' : (inc
                                                .amount !== null ? '৳ ' + inc.amount
                                                .toLocaleString('en-US', {
                                                    minimumFractionDigits: 2
                                                }) : '-');
                                        let formattedDate = inc.start_date ?
                                            formatDateStr(inc.start_date) : '-';
                                        incHtml += `
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge npv-inc-badge npv-inc-badge-new" style="margin-left: 0;">${ord}</span>
                                                </td>
                                                <td class="text-center fs-3xs">${formattedDate}</td>
                                                <td class="text-end fw-semibold text-warning fs-3xs">${escStr}</td>
                                                <td class="text-end num-cell font-bold fs-3xs">৳ ${inc.incremented_amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>`;
                                    });
                                    incHtml += `</tbody></table></div>`;
                                    $('#modalAuditIncrementsBox').html(incHtml);
                                } else {
                                    $('#modalAuditIncBadge').text('0 Defined');
                                    $('#modalAuditIncrementsBox').html(
                                        '<em class="text-muted fs-3xs">No rent escalation cycles configured. Base rent remains constant.</em>'
                                    );
                                }
                            }

                            // Populate Footer Totals matching Workbench
                            $('#modalFootTotalGross').text('৳ ' + res.total_gross_rent
                                .toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalFootAdvanceDeduction').text('-৳ ' + res
                                .total_advance_deductions.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalFootDepositRefund').text('-৳ ' + res.total_deposit_refunds
                                .toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalFootNetOutflow').text('৳ ' + res.total_undiscounted_outflow
                                .toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));
                            $('#modalFootTotalNPV').text('৳ ' + res.total_npv.toLocaleString(
                                'en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));

                            // Toggle increment legend
                            let hasIncrements = res.cash_flows && res.cash_flows.some(cf => cf
                                .increment_cycle > 0);
                            if (hasIncrements) {
                                $('#modalIncLegend').removeClass('d-none');
                            } else {
                                $('#modalIncLegend').addClass('d-none');
                            }

                            // Build table rows (matching Workbench 13-column format)
                            let rowsHtml = '';
                            if (res.cash_flows && res.cash_flows.length > 0) {
                                res.cash_flows.forEach(function(cf) {
                                    let incBadge = '';
                                    if (cf.increment_cycle > 0) {
                                        let badgeClass = cf
                                            .increment_starts_this_month ?
                                            'npv-inc-badge-new' : 'npv-inc-badge-carry';
                                        let ord = getOrdinal(cf.increment_cycle);
                                        let tooltipText =
                                            `${ord} rent increment (cycle ${cf.increment_cycle} of ${cf.total_increment_cycles}). Cumulative uplift over base rent: ${cf.increment_uplift_pct.toFixed(2)}%.${cf.increment_starts_this_month ? ' Takes effect this month.' : ' Carried forward.'}`;
                                        incBadge =
                                            ` <span class="badge npv-inc-badge ${badgeClass}" title="${tooltipText}"><i class="fa fa-arrow-up"></i> ${ord}</span>`;
                                    }

                                    let rowClass = cf.is_deferred ?
                                        'table-light text-muted opacity-75' : (cf
                                            .arrears_amount > 0 ?
                                            'table-warning-light' : '');
                                    let statusBadge = '';
                                    if (cf.is_deferred) {
                                        statusBadge =
                                            ' <span class="badge bg-secondary ms-1 fs-3xs" title="Deferred Payment Period"><i class="fa fa-clock"></i> Deferred</span>';
                                    } else if (cf.arrears_amount > 0) {
                                        statusBadge =
                                            ` <span class="badge bg-warning text-dark ms-1 fs-3xs" title="Includes ৳ ${cf.arrears_amount.toLocaleString('en-US', {minimumFractionDigits: 2})} arrears"><i class="fa fa-hand-holding-usd"></i> Arrears Paid</span>`;
                                    }

                                    rowsHtml += `
                                        <tr class="${rowClass}">
                                            <td class="text-center text-muted num-cell fs-xs npv-col-left-1">${cf.period_index}</td>
                                            <td class="text-center fw-semibold fs-xs npv-col-left-2">${cf.month_label}${statusBadge}${incBadge}</td>
                                            <td class="text-end num-cell npv-col-breakdown">${cf.office_gross_rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell npv-col-breakdown">${cf.dg_gross_rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell npv-col-breakdown">${cf.parking_gross_rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell npv-col-breakdown">${cf.store_gross_rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell fw-semibold npv-total-col">${cf.total_gross_rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell text-danger">${cf.advance_deduction > 0 ? '-' + cf.advance_deduction.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                            <td class="text-end num-cell text-success">${cf.deposit_refund > 0 ? '-' + cf.deposit_refund.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                            <td class="text-end num-cell fw-bold">${cf.net_outflow.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-center num-cell text-muted fs-xs">${cf.discount_factor.toFixed(6)}</td>
                                            <td class="text-end num-cell text-primary fw-bold npv-col-right-2">৳ ${cf.present_value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end num-cell text-info fs-xs npv-col-right-1">৳ ${cf.cumulative_pv.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                rowsHtml =
                                    '<tr><td colspan="13" class="text-center text-muted py-3 fs-xs">No cashflow periods calculated.</td></tr>';
                            }

                            $('#modalCashflowRows').html(rowsHtml);
                            $('#modalMainContent').removeClass('d-none');
                        } else {
                            $('#modalErrorMessage').text(response.message ||
                                'Failed to load details.');
                            $('#modalErrorAlert').removeClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        $('#modalLoadingSpinner').addClass('d-none');
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message :
                            'Server error loading agreement details.';
                        $('#modalErrorMessage').text(errorMsg);
                        $('#modalErrorAlert').removeClass('d-none');
                    }
                });
            });

            // Set NPV Interest Rate Modal Handler
            const setRateModal = new bootstrap.Modal(document.getElementById('setInterestRateModal'));

            $(document).on('click', '.set-rate-trigger-btn', function() {
                const agrId = $(this).data('id');
                const refNo = $(this).data('ref');
                const currentRate = $(this).data('current-rate');

                $('#modalSetRateAgreementId').val(agrId);
                $('#modalSetRateRefNo').text(refNo);
                $('#modalInterestRateInput').val(currentRate !== '' && currentRate !== null &&
                    currentRate !== undefined ? currentRate : '');
                $('#setRateAlertBox').addClass('d-none').text('');

                if (!currentRate) {
                    $('#btnClearRateBtn').addClass('d-none');
                } else {
                    $('#btnClearRateBtn').removeClass('d-none');
                }

                setRateModal.show();
            });

            // Clear Rate button
            $('#btnClearRateBtn').on('click', function() {
                if (confirm(
                        'Are you sure you want to clear the NPV interest rate for this agreement? NPV will not be calculated.'
                    )) {
                    submitRateUpdate(null);
                }
            });

            // Form submit
            $('#setInterestRateForm').on('submit', function(e) {
                e.preventDefault();
                const rateVal = $('#modalInterestRateInput').val();
                if (!rateVal || parseFloat(rateVal) <= 0) {
                    $('#setRateAlertBox').removeClass('d-none').text(
                        'Please enter a valid interest rate greater than 0, or click Clear Rate.');
                    return;
                }
                submitRateUpdate(rateVal);
            });

            function submitRateUpdate(rate) {
                const agrId = $('#modalSetRateAgreementId').val();
                const btn = $('#btnSaveInterestRate');
                const originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
                $('#setRateAlertBox').addClass('d-none');

                $.ajax({
                    url: '/facilities/npv/agreements/' + agrId + '/update-interest-rate',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        interest_rate: rate
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(originalText);
                        if (response.success && response.data) {
                            const data = response.data;
                            setRateModal.hide();

                            // Update Row in Table
                            const rowElem = $(`#row-agreement-${agrId}`);
                            if (rowElem.length) {
                                // Update Rate Cell (Col 9)
                                let rateHtml = '';
                                if (data.has_interest_rate) {
                                    rateHtml = `
                                        <span class="badge bg-primary-light text-white fw-semibold fs-2xs px-2 py-1 rate-display-badge">
                                            ${data.interest_rate_formatted}
                                        </span>
                                        <button type="button" class="btn btn-xs btn-link text-primary p-0 ms-1 set-rate-trigger-btn"
                                            data-id="${agrId}"
                                            data-ref="${data.agreement_ref_no}"
                                            data-current-rate="${data.interest_rate}"
                                            title="Edit NPV interest rate">
                                            <i class="fa fa-pen fs-3xs"></i>
                                        </button>
                                    `;
                                } else {
                                    rateHtml = `
                                        <button type="button" class="btn btn-xs btn-outline-warning text-dark py-0 px-2 set-rate-trigger-btn"
                                            data-id="${agrId}"
                                            data-ref="${data.agreement_ref_no}"
                                            data-current-rate=""
                                            title="Set NPV interest rate for this agreement">
                                            <i class="fa fa-plus me-1"></i>Set Rate
                                        </button>
                                    `;
                                }

                                // Update NPV Cell (Col 10)
                                let npvHtml = '';
                                if (data.has_interest_rate && data.total_npv !== null) {
                                    npvHtml =
                                        `<span class="npv-value-cell font-bold text-primary">${data.total_npv_formatted}</span>`;
                                } else {
                                    npvHtml =
                                        `<span class="badge bg-light text-muted border fst-italic fs-3xs">Rate not set</span>`;
                                }

                                const rateCell = rowElem.find('.rate-cell');
                                rateCell.html(rateHtml).attr('data-order', data.has_interest_rate ? data
                                    .interest_rate : -1);

                                const npvCell = rowElem.find('.npv-cell');
                                npvCell.html(npvHtml).attr('data-order', data.has_interest_rate && data
                                    .total_npv !== null ? data.total_npv : -1);

                                // Update Actions Cell
                                let actionsHtml = `
                                    <div class="btn-group btn-group-sm">
                                        {{-- View option commented out
                                        <button type="button" class="btn btn-xs btn-alt-primary view-detail-btn px-2 py-1">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        --}}
                                        <a href="/facilities/npv?agreement_id=${agrId}"
                                            class="btn btn-xs btn-alt-secondary px-2 py-1"
                                            title="Open in Calculation Workbench">
                                            <i class="fa fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                `;
                                rowElem.find('td:last').html(actionsHtml);

                                // Invalidate DataTables cache for row so sorting respects new values
                                table.row(rowElem).invalidate();
                            }

                            // Update KPI Cards
                            $('#kpiPortfolioTotalNPV').text(data.portfolio_total_npv).attr('title', data
                                .portfolio_total_npv);
                            $('#kpiCalculatedSubtext').text(
                                `${data.calculated_count} of ${data.total_agreements} leases calculated`
                            );
                            $('#kpiLeasesSubtext').text(
                                `${data.calculated_count} with rate (${data.total_agreements - data.calculated_count} pending)`
                            );
                            $('#kpiAverageNPV').text(data.average_npv).attr('title', data.average_npv);
                            $('#footPortfolioTotalNPV').text(data.portfolio_total_npv);
                            $('#footPortfolioTotalOutflow').text(data.portfolio_total_outflow);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(originalText);
                        const errMsg = xhr.responseJSON ? xhr.responseJSON.message :
                            'Error updating interest rate.';
                        $('#setRateAlertBox').removeClass('d-none').text(errMsg);
                    }
                });
            }
        });
    </script>
@endsection
