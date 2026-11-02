@extends('Partials.app', ['activeMenu' => 'facilities.npv.summary'])

@section('title')
    NPV Summary Schedule - {{ config('app.name') }}
@endsection

@section('page_title')
    NPV Summary Schedule
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/npv-calculation.css') }}">
    <style>
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .metric-dropdown-wrapper {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.35rem 0.65rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .metric-dropdown-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            letter-spacing: 0.03em;
            margin: 0;
            white-space: nowrap;
        }

        .metric-select-highlight {
            font-weight: 700;
            color: #1e293b;
            border-color: #94a3b8;
            background-color: #ffffff;
            min-width: 280px;
        }

        .metric-select-highlight:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <!-- Header -->
        <div class="npv-report-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <div class="npv-report-eyebrow">
                    <i class="fa fa-calendar-alt me-1"></i> Facilities Management &bull; Financial Analytics
                </div>
                <h2 class="npv-report-title">NPV Summary Schedule</h2>
                <p class="npv-report-sub">
                    Consolidated multi-agreement monthly schedule across active lease agreements aligned along chronological
                    calendar months.
                </p>
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

        <!-- ====================================================================
                     CONSOLIDATED MULTI-AGREEMENT TIMELINE MATRIX REPORT
                     ==================================================================== -->
        <div class="npv-summary-card position-relative" id="matrixSection">
            <div class="loading-overlay d-none" id="matrixLoadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading matrix data...</span>
                </div>
            </div>

            <!-- Card Header: Title + Primary Drop Down Menu -->
            <div class="npv-summary-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h3 class="npv-summary-card-title mb-0">
                        <i class="fa fa-table text-success"></i>
                        Consolidated Schedule Matrix
                    </h3>

                    <!-- Primary Dropdown Menu (Matches reference Excel sheet) -->
                    <div class="metric-dropdown-wrapper">
                        <label for="matrixMetricSelect" class="metric-dropdown-label">
                            <i class="fa fa-caret-down text-primary me-1"></i> Drop down menu:
                        </label>
                        <select id="matrixMetricSelect" class="form-select form-select-sm metric-select-highlight">
                            @foreach ($metrics as $k => $m)
                                <option value="{{ $k }}" {{ $selectedMetric == $k ? 'selected' : '' }}>
                                    {{ $m['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnExportMatrix"
                        title="Export current schedule to CSV">
                        <i class="fa fa-file-csv me-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="npv-controls-bar border-0 border-bottom rounded-0 mb-0 py-2 px-3 bg-light">
                <div class="row g-2 align-items-center">
                    <!-- Timeline Horizon Filter -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="matrixYearSelect" class="form-label fs-xs fw-semibold mb-0 text-nowrap text-muted">
                                <i class="fa fa-calendar me-1 text-info"></i> Timeline:
                            </label>
                            <select id="matrixYearSelect" class="form-select form-select-sm">
                                <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>All Active Years
                                </option>
                                @foreach ($initialMatrix['available_years'] as $yr)
                                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Year
                                        {{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Location Filter -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="d-flex align-items-center gap-2">
                            <label for="matrixBuildingSelect"
                                class="form-label fs-xs fw-semibold mb-0 text-nowrap text-muted">
                                <i class="fa fa-building me-1 text-secondary"></i> Location:
                            </label>
                            <select id="matrixBuildingSelect" class="form-select form-select-sm">
                                <option value="">All Locations</option>
                                @foreach ($buildings as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->site_name ?: ($b->code ?: 'Building #' . $b->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="col-12 col-sm-12 col-lg-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="text" id="matrixSearchInput" class="form-control border-start-0"
                                placeholder="Search agreement code or land owner...">
                            <button class="btn btn-outline-secondary" type="button" id="btnSearchMatrix">
                                Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Metric Banner Strip -->
            <div
                class="px-3 py-2 border-bottom bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 fs-xs">
                <div>
                    <span class="text-muted">Displaying Schedule:</span>
                    <strong class="text-primary ms-1" id="activeMetricTitle">{{ $initialMatrix['metric_label'] }}</strong>
                    <span class="badge bg-secondary ms-2" id="activeTimelineBadge">
                        {{ $initialMatrix['selected_year'] === 'all' ? 'All Active Years' : 'Year ' . $initialMatrix['selected_year'] }}
                    </span>
                </div>
                <div class="text-muted fst-italic">
                    <i class="fa fa-info-circle me-1 text-info"></i>
                    Values are rounded to whole numbers (except discount factor: 4 decimal places). Hyphen (-) indicates
                    lease was not active in that calendar month.
                </div>
            </div>

            <!-- Consolidated Matrix Table Viewport -->
            <div class="npv-timeline-viewport" id="matrixViewport">
                <table class="npv-timeline-table npv-matrix-table" id="matrixTable">
                    <thead>
                        <tr id="matrixTheadRow">
                            <th class="col-code">Agreement code</th>
                            <th>Land owner name</th>
                            <th>Location</th>
                            <th>Space type</th>
                            <th>Floor no.</th>
                            <th class="text-end">Space sft</th>
                            @foreach ($initialMatrix['columns'] as $col)
                                <th class="text-center" title="{{ $col['month_name'] }}">{{ $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="matrixTbody">
                        @foreach ($initialMatrix['rows'] as $r)
                            <tr>
                                <td class="col-code">
                                    <a href="{{ route('facilities.npv.index', ['agreement_id' => $r['id']]) }}"
                                        class="text-primary fw-semibold" title="Open Agreement in Calculation Workbench"
                                        target="_blank">
                                        {{ $r['agreement_code'] }}
                                        <i class="fa fa-external-link-alt ms-1 fs-xs text-muted"></i>
                                    </a>
                                </td>
                                <td>{{ $r['land_owner'] }}</td>
                                <td>{{ $r['location'] }}</td>
                                <td>{{ $r['space_type'] }}</td>
                                <td>{{ $r['floor_no'] }}</td>
                                <td class="cell-num">{{ number_format($r['space_sft'], 0) }}</td>
                                @foreach ($initialMatrix['columns'] as $col)
                                    @php $val = $r['values'][$col['key']] ?? null; @endphp
                                    @if ($val === null)
                                        <td class="cell-hyphen">-</td>
                                    @elseif ($initialMatrix['decimals'] === 4)
                                        <td class="cell-num">{{ number_format($val, 4) }}</td>
                                    @else
                                        <td class="cell-num">{{ number_format($val, 0) }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Strip (Record Count, NO TOTAL ROW) -->
            <div class="p-2 px-3 bg-light text-muted fs-xs d-flex justify-content-between align-items-center">
                <span id="matrixRecordCount">Showing {{ count($initialMatrix['rows']) }} lease agreement(s)</span>
                <span class="text-muted"><i class="fa fa-check-circle text-success me-1"></i> Multi-agreement calendar
                    alignment active</span>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const matrixDataUrl = "{{ route('facilities.npv.summary.matrix-data') }}";
            const exportUrl = "{{ route('facilities.npv.summary.export') }}";
            const workbenchUrl = "{{ route('facilities.npv.index') }}";

            // Helper to format numbers (0 decimals for currency, 4 for discount factor)
            function formatNumber(val, decimals = 0) {
                if (val === null || val === undefined || val === '-') return '-';
                const num = Number(val);
                if (isNaN(num)) return val;
                return num.toLocaleString('en-US', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            let searchTimeout = null;

            function reloadMatrix() {
                $('#matrixLoadingOverlay').removeClass('d-none');

                const metric = $('#matrixMetricSelect').val();
                const year = $('#matrixYearSelect').val();
                const buildingId = $('#matrixBuildingSelect').val();
                const search = $('#matrixSearchInput').val();

                $.ajax({
                    url: matrixDataUrl,
                    type: 'GET',
                    data: {
                        metric: metric,
                        year: year,
                        building_id: buildingId,
                        search: search
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#matrixLoadingOverlay').addClass('d-none');
                        if (res.success && res.matrix) {
                            renderMatrixTable(res.matrix);
                        }
                    },
                    error: function() {
                        $('#matrixLoadingOverlay').addClass('d-none');
                        alert('Failed to reload schedule matrix data.');
                    }
                });
            }

            function renderMatrixTable(matrix) {
                // Update active metric title & badge
                $('#activeMetricTitle').text(matrix.metric_label);
                $('#activeTimelineBadge').text(matrix.selected_year === 'all' ? 'All Active Years' : 'Year ' +
                    matrix.selected_year);

                // Update Year options if new years detected
                const currentYear = matrix.selected_year;
                let yearOptions =
                    `<option value="all" ${currentYear === 'all' ? 'selected' : ''}>All Active Years</option>`;
                matrix.available_years.forEach(function(yr) {
                    yearOptions +=
                        `<option value="${yr}" ${currentYear == yr ? 'selected' : ''}>Year ${yr}</option>`;
                });
                $('#matrixYearSelect').html(yearOptions);

                // Build Table Header
                let theadHtml = `
                    <th class="col-code">Agreement code</th>
                    <th>Land owner name</th>
                    <th>Location</th>
                    <th>Space type</th>
                    <th>Floor no.</th>
                    <th class="text-end">Space sft</th>
                `;

                matrix.columns.forEach(function(col) {
                    theadHtml += `<th class="text-center" title="${col.month_name}">${col.label}</th>`;
                });
                $('#matrixTheadRow').html(theadHtml);

                // Build Table Body (NO TOTAL ROW AT THE BOTTOM)
                let tbodyHtml = '';
                if (matrix.rows.length === 0) {
                    const colCount = 6 + matrix.columns.length;
                    tbodyHtml =
                        `<tr><td colspan="${colCount}" class="text-center text-muted py-4">No lease agreements matched the selected criteria.</td></tr>`;
                } else {
                    matrix.rows.forEach(function(r) {
                        const linkUrl = `${workbenchUrl}?agreement_id=${r.id}`;
                        tbodyHtml += '<tr>';
                        tbodyHtml +=
                            `<td class="col-code"><a href="${linkUrl}" class="text-primary fw-semibold" title="Open Agreement in Calculation Workbench" target="_blank">${r.agreement_code} <i class="fa fa-external-link-alt ms-1 fs-xs text-muted"></i></a></td>`;
                        tbodyHtml += `<td>${r.land_owner}</td>`;
                        tbodyHtml += `<td>${r.location}</td>`;
                        tbodyHtml += `<td>${r.space_type}</td>`;
                        tbodyHtml += `<td>${r.floor_no}</td>`;
                        tbodyHtml += `<td class="cell-num">${formatNumber(r.space_sft, 0)}</td>`;

                        matrix.columns.forEach(function(col) {
                            const val = r.values[col.key];
                            if (val === null || val === undefined) {
                                tbodyHtml += '<td class="cell-hyphen">-</td>';
                            } else {
                                tbodyHtml +=
                                    `<td class="cell-num">${formatNumber(val, matrix.decimals)}</td>`;
                            }
                        });
                        tbodyHtml += '</tr>';
                    });
                }

                $('#matrixTbody').html(tbodyHtml);
                $('#matrixRecordCount').text(`Showing ${matrix.rows.length} lease agreement(s)`);
            }

            // Metric & Filter Event Handlers
            $('#matrixMetricSelect, #matrixYearSelect, #matrixBuildingSelect').on('change', function() {
                reloadMatrix();
            });

            $('#matrixSearchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    reloadMatrix();
                }, 350);
            });

            $('#btnSearchMatrix').on('click', function() {
                reloadMatrix();
            });

            // Export Schedule Matrix to CSV
            $('#btnExportMatrix').on('click', function() {
                const metric = $('#matrixMetricSelect').val();
                const year = $('#matrixYearSelect').val();
                const buildingId = $('#matrixBuildingSelect').val();
                const search = $('#matrixSearchInput').val();

                const query = $.param({
                    metric: metric,
                    year: year,
                    building_id: buildingId,
                    search: search
                });

                window.location.href = exportUrl + '?' + query;
            });
        });
    </script>
@endsection
