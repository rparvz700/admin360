@extends('Partials.app', ['activeMenu' => 'reports-hub'])

@section('title')
    Reports Hub - Published & Saved Reports
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
<style>
    /* ============================================================
       REPORTS HUB STYLING (ONEUI / BOOTSTRAP 5)
       ============================================================ */
    .hub-header-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        color: #ffffff;
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.15);
    }
    .hub-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .hub-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .report-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .report-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -3px rgba(0,0,0,0.08);
        border-color: #93c5fd;
    }
    .report-card.is-active {
        border: 2px solid #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
    .report-card-body {
        padding: 18px 20px;
        flex-grow: 1;
    }
    .report-card-footer {
        padding: 12px 20px;
        background-color: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .report-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .col-pill {
        display: inline-block;
        padding: 2px 7px;
        font-size: 11px;
        font-weight: 500;
        border-radius: 4px;
        background-color: #f1f5f9;
        color: #475569;
        margin: 2px 2px 2px 0;
        border: 1px solid #e2e8f0;
    }
    .table-pill {
        display: inline-block;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-radius: 4px;
        background-color: #eff6ff;
        color: #2563eb;
        margin: 2px 2px 2px 0;
        border: 1px solid #bfdbfe;
    }
    .date-preset-pill {
        cursor: pointer;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
    }
    .date-preset-pill:hover, .date-preset-pill.active {
        background-color: #3b82f6;
        color: #ffffff;
        border-color: #2563eb;
    }
    .runner-container {
        scroll-margin-top: 80px;
    }
    .table-runner thead th {
        background-color: #f8fafc;
        color: #1e293b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
    }
    .table-runner tbody td {
        font-size: 13px;
        vertical-align: middle;
    }
    .shimmer-placeholder {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        height: 16px;
        border-radius: 4px;
    }
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endsection

@section('content')
<div class="content">

    <!-- Hero / Header Banner -->
    <div class="hub-header-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-uppercase font-monospace" style="font-size: 10px; letter-spacing: 0.5px;">Analytics Portal</span>
                <span class="text-white-50 fs-xs"><i class="fa fa-shield-halved me-1"></i> Pre-Authorized Datasets</span>
            </div>
            <h1 class="h3 fw-bold text-white mb-1">
                <i class="fa fa-table-list text-primary-light me-2"></i> Reports Hub
            </h1>
            <p class="text-white-75 mb-0 fs-sm" style="max-width: 680px;">
                Browse standard published reports, filter by custom date windows, view aggregated metrics, and download clean reports directly to Excel or CSV.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @canany(['manage-report-templates'])
                <a href="{{ route('reports.builder') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-sliders me-1"></i> Open Report Builder
                </a>
            @endcanany
            @canany(['manage-data-dictionary'])
                <a href="{{ route('reports.dictionary') }}" class="btn btn-sm btn-alt-secondary text-white">
                    <i class="fa fa-book me-1"></i> Data Dictionary
                </a>
            @endcanany
        </div>
    </div>

    <!-- Quick Metrics Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="hub-stat-card d-flex align-items-center gap-3">
                <div class="report-icon-box bg-success-light text-success">
                    <i class="fa fa-globe"></i>
                </div>
                <div>
                    <div class="fs-xs fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px;">Public Shared Reports</div>
                    <div class="fs-4 fw-bold text-dark">{{ $sharedTemplates->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="hub-stat-card d-flex align-items-center gap-3">
                <div class="report-icon-box bg-primary-light text-primary">
                    <i class="fa fa-lock"></i>
                </div>
                <div>
                    <div class="fs-xs fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px;">My Private Reports</div>
                    <div class="fs-4 fw-bold text-dark">{{ $myTemplates->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="hub-stat-card d-flex align-items-center gap-3">
                <div class="report-icon-box bg-info-light text-info">
                    <i class="fa fa-file-excel"></i>
                </div>
                <div>
                    <div class="fs-xs fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px;">Instant Export Formats</div>
                    <div class="d-flex gap-2 mt-1">
                        <span class="badge bg-success-light text-success font-monospace fs-xs">Excel (.xlsx)</span>
                        <span class="badge bg-secondary-light text-secondary font-monospace fs-xs">CSV (.csv)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catalog Toolbar (Filter Tabs & Search Input) -->
    <div class="block block-rounded block-bordered mb-4">
        <div class="block-content p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Filter Pills -->
                <div class="col-md-6 col-lg-5">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-alt-secondary active tab-filter-btn" data-filter="all">
                            All Reports <span class="badge bg-secondary ms-1">{{ $templates->count() }}</span>
                        </button>
                        <button type="button" class="btn btn-alt-secondary tab-filter-btn" data-filter="public">
                            <i class="fa fa-globe text-success me-1"></i> Public <span class="badge bg-success ms-1">{{ $sharedTemplates->count() }}</span>
                        </button>
                        <button type="button" class="btn btn-alt-secondary tab-filter-btn" data-filter="private">
                            <i class="fa fa-lock text-muted me-1"></i> Private <span class="badge bg-primary ms-1">{{ $myTemplates->count() }}</span>
                        </button>
                    </div>
                </div>

                <!-- Live Search Box -->
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="hub-search-input"
                               placeholder="Search reports by title, creator, or fields...">
                        <button type="button" class="btn btn-alt-secondary" id="btn-clear-search" style="display: none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Grid / Catalog -->
    <div class="row g-3 mb-4" id="templates-grid">
        @forelse($templates as $tpl)
            @php
                $isShared = (bool)$tpl->is_shared;
                $tables = $tpl->tables_summary ?? [];
                $columns = $tpl->columns_summary ?? [];
                $creatorName = $tpl->creator->name ?? 'System Admin';
                $canEdit = ($tpl->created_by === auth()->id() || auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage-report-templates'));
            @endphp
            <div class="col-md-6 col-xl-4 template-card-item"
                 data-id="{{ $tpl->id }}"
                 data-visibility="{{ $isShared ? 'public' : 'private' }}"
                 data-search="{{ strtolower($tpl->name . ' ' . $tpl->description . ' ' . $creatorName . ' ' . implode(' ', $tables) . ' ' . implode(' ', $columns)) }}">
                <div class="report-card" id="card-tpl-{{ $tpl->id }}">
                    <div class="report-card-body">
                        <!-- Card Header & Badge -->
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="report-icon-box {{ $isShared ? 'bg-success-light text-success' : 'bg-primary-light text-primary' }}">
                                    <i class="fa {{ $isShared ? 'fa-chart-pie' : 'fa-folder-closed' }}"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark fs-base" title="{{ $tpl->name }}">
                                        {{ $tpl->name }}
                                    </h5>
                                    <div class="fs-xs text-muted">
                                        by <span class="fw-semibold">{{ $creatorName }}</span>
                                    </div>
                                </div>
                            </div>
                            @if($isShared)
                                <span class="badge bg-success-light text-success fs-xs flex-shrink-0">
                                    <i class="fa fa-globe me-1"></i> Public
                                </span>
                            @else
                                <span class="badge bg-secondary-light text-secondary fs-xs flex-shrink-0">
                                    <i class="fa fa-lock me-1"></i> Private
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="fs-xs text-muted mb-3" style="min-height: 36px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $tpl->description }}">
                            {{ $tpl->description ?: 'Standard customized report generated with selected dimensions and filters.' }}
                        </p>

                        <!-- Tables summary -->
                        @if(!empty($tables))
                            <div class="mb-2">
                                <div class="fs-xs text-muted mb-1 fw-semibold">Tables:</div>
                                <div>
                                    @foreach(array_slice($tables, 0, 3) as $tbl)
                                        <span class="table-pill">{{ $tbl }}</span>
                                    @endforeach
                                    @if(count($tables) > 3)
                                        <span class="badge bg-light text-muted fs-xs">+{{ count($tables) - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Columns summary -->
                        @if(!empty($columns))
                            <div>
                                <div class="fs-xs text-muted mb-1 fw-semibold">Columns:</div>
                                <div class="text-truncate">
                                    @foreach(array_slice($columns, 0, 4) as $col)
                                        <span class="col-pill">{{ $col }}</span>
                                    @endforeach
                                    @if(count($columns) > 4)
                                        <span class="col-pill">+{{ count($columns) - 4 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Card Actions Footer -->
                    <div class="report-card-footer">
                        <span class="fs-xs text-muted" title="{{ $tpl->updated_at }}">
                            <i class="fa fa-clock me-1"></i>{{ $tpl->updated_at->diffForHumans() }}
                        </span>
                        <div class="d-flex align-items-center gap-1">
                            <!-- Run Button -->
                            <button type="button"
                                    class="btn btn-xs btn-primary btn-run-card"
                                    data-id="{{ $tpl->id }}"
                                    data-name="{{ $tpl->name }}"
                                    data-desc="{{ $tpl->description }}"
                                    data-template='@json($tpl)'>
                                <i class="fa fa-play me-1"></i> Run
                            </button>

                            <!-- Direct Export Dropdown -->
                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn-xs btn-alt-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="Export Report Directly">
                                    <i class="fa fa-download"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end fs-xs">
                                    <li>
                                        <a class="dropdown-item btn-direct-export" href="javascript:void(0)" data-id="{{ $tpl->id }}" data-format="excel">
                                            <i class="fa fa-file-excel text-success me-2"></i> Export Excel (.xlsx)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item btn-direct-export" href="javascript:void(0)" data-id="{{ $tpl->id }}" data-format="csv">
                                            <i class="fa fa-file-csv text-primary me-2"></i> Export CSV (.csv)
                                        </a>
                                    </li>
                                    @if($canEdit)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('reports.builder', ['template' => $tpl->id]) }}">
                                                <i class="fa fa-sliders text-muted me-2"></i> Edit in Builder
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12" id="no-templates-notice">
                <div class="block block-rounded block-bordered text-center py-5">
                    <div class="block-content">
                        <div class="mb-3 text-muted">
                            <i class="fa fa-folder-open fa-3x"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-1">No Published Reports Available</h4>
                        <p class="text-muted fs-sm mb-4">
                            There are no saved report templates yet. Administrators can author and publish templates using the Report Builder Studio.
                        </p>
                        @canany(['manage-report-templates'])
                            <a href="{{ route('reports.builder') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-sliders me-1"></i> Create First Report in Builder
                            </a>
                        @endcanany
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Empty Filter Notice (Hidden by default) -->
    <div id="search-empty-state" class="block block-rounded block-bordered text-center py-4 d-none mb-4">
        <div class="block-content">
            <i class="fa fa-search fa-2x text-muted mb-2"></i>
            <h5 class="fw-semibold mb-1">No matching reports found</h5>
            <p class="text-muted fs-sm mb-0">Try adjusting your keywords or clearing the active filter.</p>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- INTERACTIVE REPORT RUNNER & INSTANT PREVIEW SECTION      -->
    <!-- ======================================================== -->
    <div class="runner-container" id="report-runner-section">
        <!-- Standby Hero Banner (Shown when no report is selected) -->
        <div id="runner-placeholder" class="block block-rounded block-bordered border-dashed bg-light text-center py-5">
            <div class="block-content">
                <div class="mb-3 text-primary opacity-50">
                    <i class="fa fa-chart-column fa-3x"></i>
                </div>
                <h4 class="h5 fw-bold text-dark mb-1">Interactive Report Viewer</h4>
                <p class="text-muted fs-sm mb-0" style="max-width: 500px; margin: 0 auto;">
                    Click <strong>Run</strong> on any report template above to preview live database records, adjust runtime date ranges, and export with zero limits.
                </p>
            </div>
        </div>

        <!-- Active Report Viewer (Shown when a report is executed) -->
        <div id="runner-active-panel" class="block block-rounded block-bordered d-none shadow-sm">
            <!-- Runner Header -->
            <div class="block-header block-header-default border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="report-icon-box bg-primary-light text-primary" style="width: 34px; height: 34px; font-size: 0.9rem;">
                        <i class="fa fa-table"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="block-title fw-bold fs-base mb-0" id="runner-report-title">
                                Report Name
                            </h3>
                            <span class="badge bg-primary-light text-primary fs-xs font-monospace" id="runner-report-badge">
                                Active
                            </span>
                        </div>
                        <div class="fs-xs text-muted" id="runner-report-desc">
                            Description goes here
                        </div>
                    </div>
                </div>

                <!-- Export & Refresh Toolbar -->
                <div class="block-options d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-alt-primary" id="btn-runner-refresh" title="Re-run query">
                        <i class="fa fa-arrows-rotate me-1"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-runner-export-excel" title="Download Excel (.xlsx)">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-alt-secondary" id="btn-runner-export-csv" title="Download CSV (.csv)">
                        <i class="fa fa-file-csv me-1"></i> CSV
                    </button>
                    @canany(['manage-report-templates'])
                        <a href="javascript:void(0)" class="btn btn-sm btn-alt-secondary" id="btn-runner-open-builder" title="Open this report in Builder Studio">
                            <i class="fa fa-sliders me-1"></i> Studio
                        </a>
                    @endcanany
                </div>
            </div>

            <!-- Runtime Date Range Filter Toolbar -->
            <div class="block-content bg-light border-bottom py-2 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fs-xs fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                            <i class="fa fa-calendar me-1"></i> Runtime Date Filter:
                        </span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar-day fs-xs"></i></span>
                            <input type="text" class="form-control bg-white js-hub-flatpickr" id="runner-date-from" placeholder="From Date">
                        </div>
                        <span class="fs-xs text-muted">to</span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar-day fs-xs"></i></span>
                            <input type="text" class="form-control bg-white js-hub-flatpickr" id="runner-date-to" placeholder="To Date">
                        </div>

                        <!-- Date Column Selector (if multiple date fields exist) -->
                        <div style="width: 180px;" id="date-col-wrapper" class="d-none">
                            <select class="form-select form-select-sm" id="runner-date-col-select" title="Date Field to Filter">
                            </select>
                        </div>

                        <button type="button" class="btn btn-xs btn-primary" id="btn-apply-date-filter">
                            <i class="fa fa-filter me-1"></i> Apply
                        </button>
                        <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-clear-date-filter" title="Clear Date Window">
                            <i class="fa fa-rotate-left"></i>
                        </button>
                    </div>

                    <!-- Quick Preset Badges -->
                    <div class="d-flex align-items-center gap-1">
                        <span class="date-preset-pill" data-preset="today">Today</span>
                        <span class="date-preset-pill" data-preset="this_month">This Month</span>
                        <span class="date-preset-pill" data-preset="last_month">Last Month</span>
                        <span class="date-preset-pill" data-preset="this_year">This Year</span>
                        <span class="date-preset-pill" data-preset="all">All Dates</span>
                    </div>
                </div>
            </div>

            <!-- Table Search & Counter Toolbar -->
            <div class="block-content py-2 px-3 bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-light"><i class="fa fa-search fs-xs"></i></span>
                            <input type="text" class="form-control" id="runner-table-search" placeholder="Search within results...">
                        </div>
                        <span class="fs-xs text-muted" id="runner-stats-counter">
                            Loading...
                        </span>
                    </div>
                    <div>
                        <span class="badge bg-light text-muted font-monospace fs-xs" id="runner-base-table-badge">
                            Base: -
                        </span>
                    </div>
                </div>
            </div>

            <!-- Live Table Content -->
            <div class="block-content p-0">
                <!-- Loading State -->
                <div id="runner-loading-state" class="p-5 text-center d-none">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="fw-semibold text-dark fs-sm">Executing Query Engine...</div>
                    <div class="fs-xs text-muted">Compiling AST, joining relational datasets, and calculating aggregations.</div>
                </div>

                <!-- Empty Rows State -->
                <div id="runner-empty-state" class="p-5 text-center d-none">
                    <i class="fa fa-inbox fa-3x text-muted mb-2"></i>
                    <h5 class="fw-bold fs-sm mb-1">No Data Returned</h5>
                    <p class="text-muted fs-xs mb-0">No records match the active criteria and date window.</p>
                </div>

                <!-- Table Wrapper -->
                <div class="table-responsive" style="max-height: 520px;" id="runner-table-wrapper">
                    <table class="table table-hover table-striped table-vcenter align-middle fs-sm mb-0 table-runner" id="runner-preview-table">
                        <thead class="sticky-top">
                            <tr id="runner-thead-row">
                                <!-- Dynamic Headers -->
                            </tr>
                        </thead>
                        <tbody id="runner-tbody">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Runner Footer -->
            <div class="block-content block-content-full block-content-sm bg-body-light fs-xs text-muted d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa fa-info-circle me-1"></i> Preview displays first 50 matched rows. Full records are exported when downloading Excel / CSV.
                </div>
                <div class="font-monospace" id="runner-sql-footer">
                    <!-- Base Query Reference -->
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Hidden Export Form for Clean Browser File Downloads -->
<form id="hub-export-form" method="POST" action="{{ route('reports.export') }}" target="_blank" class="d-none">
    @csrf
    <input type="hidden" name="template_id" id="hub-export-template-id" value="">
    <input type="hidden" name="export_format" id="hub-export-format" value="excel">
    <input type="hidden" name="runtime_date_from" id="hub-export-date-from" value="">
    <input type="hidden" name="runtime_date_to" id="hub-export-date-to" value="">
    <input type="hidden" name="runtime_date_column" id="hub-export-date-col" value="">
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {

    // CSRF Ajax Setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Initialize Flatpickr inputs
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.js-hub-flatpickr', {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    }

    // Active Template State
    let activeTemplate = null;
    let rawRunnerRows = [];
    let runnerHeaders = [];

    // =============================================================
    // 1. TEMPLATE CATALOG SEARCH & TAB FILTERING
    // =============================================================
    $('.tab-filter-btn').on('click', function() {
        $('.tab-filter-btn').removeClass('active');
        $(this).addClass('active');
        applyCatalogFilters();
    });

    $('#hub-search-input').on('input', function() {
        const val = $(this).val().trim();
        if (val) {
            $('#btn-clear-search').show();
        } else {
            $('#btn-clear-search').hide();
        }
        applyCatalogFilters();
    });

    $('#btn-clear-search').on('click', function() {
        $('#hub-search-input').val('').trigger('input');
    });

    function applyCatalogFilters() {
        const filter = $('.tab-filter-btn.active').data('filter');
        const query = $('#hub-search-input').val().toLowerCase().trim();

        let visibleCount = 0;

        $('.template-card-item').each(function() {
            const vis = $(this).data('visibility');
            const searchData = $(this).data('search') || '';

            const matchesFilter = (filter === 'all') || (filter === vis);
            const matchesQuery = (!query) || searchData.includes(query);

            if (matchesFilter && matchesQuery) {
                $(this).removeClass('d-none');
                visibleCount++;
            } else {
                $(this).addClass('d-none');
            }
        });

        if (visibleCount === 0) {
            $('#search-empty-state').removeClass('d-none');
        } else {
            $('#search-empty-state').addClass('d-none');
        }
    }

    // =============================================================
    // 2. RUN REPORT ACTION (CARD CLICK)
    // =============================================================
    $(document).on('click', '.btn-run-card', function() {
        const card = $(this).closest('.report-card');
        $('.report-card').removeClass('is-active');
        card.addClass('is-active');

        const tpl = $(this).data('template');
        loadAndExecuteReport(tpl);
    });

    function loadAndExecuteReport(tpl) {
        activeTemplate = tpl;

        // Populate Viewer Card Header
        $('#runner-placeholder').addClass('d-none');
        $('#runner-active-panel').removeClass('d-none');
        $('#runner-report-title').text(tpl.name);
        $('#runner-report-desc').text(tpl.description || 'Pre-configured custom report query.');
        $('#runner-report-badge').text(tpl.is_shared ? 'Public Report' : 'Private Report');

        // Studio Link for Admins
        const studioBtn = $('#btn-runner-open-builder');
        if (studioBtn.length) {
            studioBtn.attr('href', `{{ url('reports/builder') }}?template=${tpl.id}`);
        }

        // Setup Runtime Date Dropdown options based on template columns
        setupDateColumnOptions(tpl);

        // Scroll smoothly to runner
        const runnerEl = document.getElementById('report-runner-section');
        if (runnerEl) {
            runnerEl.scrollIntoView({ behavior: 'smooth' });
        }

        // Execute query
        runActiveReportQuery();
    }

    function setupDateColumnOptions(tpl) {
        const fields = (tpl.query_config && tpl.query_config.selected_fields) ? tpl.query_config.selected_fields : [];
        const dateFields = fields.filter(f => {
            const col = (f.column || '').toLowerCase();
            const alias = (f.alias || '').toLowerCase();
            const dt = (f.data_type || '').toLowerCase();
            return dt === 'date' || dt === 'datetime' || dt === 'timestamp' ||
                   col.includes('date') || col.includes('created_at') || col.includes('updated_at') ||
                   alias.includes('date');
        });

        const select = $('#runner-date-col-select');
        select.empty();

        if (dateFields.length > 1) {
            $('#date-col-wrapper').removeClass('d-none');
            dateFields.forEach((df, i) => {
                const optVal = `${df.table}.${df.column}`;
                select.append(`<option value="${optVal}" ${i === 0 ? 'selected' : ''}>Date: ${df.alias || df.column}</option>`);
            });
        } else if (dateFields.length === 1) {
            $('#date-col-wrapper').addClass('d-none');
            select.append(`<option value="${dateFields[0].table}.${dateFields[0].column}" selected></option>`);
        } else {
            $('#date-col-wrapper').addClass('d-none');
        }
    }

    // =============================================================
    // 3. EXECUTE REPORT QUERY (AJAX PREVIEW)
    // =============================================================
    function runActiveReportQuery() {
        if (!activeTemplate) return;

        $('#runner-loading-state').removeClass('d-none');
        $('#runner-table-wrapper').addClass('d-none');
        $('#runner-empty-state').addClass('d-none');
        $('#runner-stats-counter').text('Executing query...');

        const dateFrom = $('#runner-date-from').val().trim();
        const dateTo   = $('#runner-date-to').val().trim();
        const dateCol  = $('#runner-date-col-select').val() || '';

        const payload = {
            template_id: activeTemplate.id,
            runtime_date_from: dateFrom,
            runtime_date_to: dateTo,
            runtime_date_column: dateCol
        };

        $.post("{{ route('reports.preview') }}", payload, function(res) {
            $('#runner-loading-state').addClass('d-none');

            if (!res.success) {
                Swal.fire('Query Error', res.message || 'Unable to execute report query.', 'error');
                return;
            }

            rawRunnerRows = res.rows || [];
            runnerHeaders = res.headers || [];

            $('#runner-base-table-badge').text(`Base Table: ${res.base_table || 'unknown'}`);
            $('#runner-stats-counter').html(`Showing <strong>${res.preview_count}</strong> of <strong>${res.total_count}</strong> records`);

            if (rawRunnerRows.length === 0) {
                $('#runner-empty-state').removeClass('d-none');
                $('#runner-table-wrapper').addClass('d-none');
            } else {
                $('#runner-empty-state').addClass('d-none');
                $('#runner-table-wrapper').removeClass('d-none');
                renderRunnerTable(runnerHeaders, rawRunnerRows);
            }
        }).fail(function(xhr) {
            $('#runner-loading-state').addClass('d-none');
            const msg = xhr.responseJSON?.message || 'Server error occurred while executing report.';
            Swal.fire('Execution Failed', msg, 'error');
        });
    }

    function renderRunnerTable(headers, rows) {
        const thead = $('#runner-thead-row');
        const tbody = $('#runner-tbody');

        thead.empty();
        tbody.empty();

        // Index column
        thead.append(`<th style="width: 50px;" class="text-center">#</th>`);

        // Dynamic Header titles
        headers.forEach(h => {
            const aggBadge = h.aggregate ? `<span class="badge bg-primary-light text-primary fs-xs ms-1">${h.aggregate.toUpperCase()}</span>` : '';
            thead.append(`
                <th title="${h.table}.${h.column}">
                    ${escapeHtml(h.alias)} ${aggBadge}
                </th>
            `);
        });

        // Dynamic Rows
        rows.forEach((row, i) => {
            let rowHtml = `<tr><td class="text-center text-muted fs-xs">${i + 1}</td>`;

            headers.forEach(h => {
                const val = row[h.key];
                let displayVal = val !== null && val !== undefined ? val : '—';
                const isNumeric = h.data_type === 'decimal' || h.data_type === 'integer' || (h.aggregate && h.aggregate !== '');

                if (isNumeric && val !== null && !isNaN(val)) {
                    if (h.data_type === 'decimal' || ['sum', 'avg'].includes(h.aggregate)) {
                        displayVal = Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    } else {
                        displayVal = Number(val).toLocaleString();
                    }
                    rowHtml += `<td class="text-end font-monospace">${escapeHtml(String(displayVal))}</td>`;
                } else {
                    rowHtml += `<td>${escapeHtml(String(displayVal))}</td>`;
                }
            });

            rowHtml += `</tr>`;
            tbody.append(rowHtml);
        });
    }

    // In-Table Live Search Filter
    $('#runner-table-search').on('input', function() {
        const q = $(this).val().toLowerCase().trim();
        if (!q) {
            renderRunnerTable(runnerHeaders, rawRunnerRows);
            return;
        }

        const filtered = rawRunnerRows.filter(row => {
            return Object.values(row).some(v => v !== null && String(v).toLowerCase().includes(q));
        });

        renderRunnerTable(runnerHeaders, filtered);
        $('#runner-stats-counter').html(`Filtered: <strong>${filtered.length}</strong> of ${rawRunnerRows.length} displayed rows`);
    });

    // Re-run Button
    $('#btn-runner-refresh').on('click', function() {
        runActiveReportQuery();
    });

    // Apply Date Range
    $('#btn-apply-date-filter').on('click', function() {
        runActiveReportQuery();
    });

    $('#btn-clear-date-filter').on('click', function() {
        $('#runner-date-from').val('');
        $('#runner-date-to').val('');
        $('.date-preset-pill').removeClass('active');
        runActiveReportQuery();
    });

    // Quick Date Presets Click
    $('.date-preset-pill').on('click', function() {
        $('.date-preset-pill').removeClass('active');
        $(this).addClass('active');

        const preset = $(this).data('preset');
        const now = new Date();
        const fromInput = $('#runner-date-from');
        const toInput = $('#runner-date-to');

        if (preset === 'today') {
            const todayStr = now.toISOString().slice(0, 10);
            fromInput.val(todayStr);
            toInput.val(todayStr);
        } else if (preset === 'this_month') {
            const y = now.getFullYear();
            const m = now.getMonth();
            const firstDay = new Date(y, m, 1).toISOString().slice(0, 10);
            const lastDay = new Date(y, m + 1, 0).toISOString().slice(0, 10);
            fromInput.val(firstDay);
            toInput.val(lastDay);
        } else if (preset === 'last_month') {
            const y = now.getFullYear();
            const m = now.getMonth() - 1;
            const firstDay = new Date(y, m, 1).toISOString().slice(0, 10);
            const lastDay = new Date(y, m + 1, 0).toISOString().slice(0, 10);
            fromInput.val(firstDay);
            toInput.val(lastDay);
        } else if (preset === 'this_year') {
            const y = now.getFullYear();
            fromInput.val(`${y}-01-01`);
            toInput.val(`${y}-12-31`);
        } else if (preset === 'all') {
            fromInput.val('');
            toInput.val('');
        }

        runActiveReportQuery();
    });

    // =============================================================
    // 4. EXPORT HANDLERS (EXCEL / CSV)
    // =============================================================
    // Direct Export from Cards
    $(document).on('click', '.btn-direct-export', function() {
        const id = $(this).data('id');
        const format = $(this).data('format');
        triggerExport(id, format);
    });

    // Runner Export Buttons
    $('#btn-runner-export-excel').on('click', function() {
        if (!activeTemplate) return;
        const dateFrom = $('#runner-date-from').val().trim();
        const dateTo   = $('#runner-date-to').val().trim();
        const dateCol  = $('#runner-date-col-select').val() || '';
        triggerExport(activeTemplate.id, 'excel', dateFrom, dateTo, dateCol);
    });

    $('#btn-runner-export-csv').on('click', function() {
        if (!activeTemplate) return;
        const dateFrom = $('#runner-date-from').val().trim();
        const dateTo   = $('#runner-date-to').val().trim();
        const dateCol  = $('#runner-date-col-select').val() || '';
        triggerExport(activeTemplate.id, 'csv', dateFrom, dateTo, dateCol);
    });

    function triggerExport(templateId, format, dateFrom = '', dateTo = '', dateCol = '') {
        $('#hub-export-template-id').val(templateId);
        $('#hub-export-format').val(format);
        $('#hub-export-date-from').val(dateFrom);
        $('#hub-export-date-to').val(dateTo);
        $('#hub-export-date-col').val(dateCol);

        One.helpers('jq-notify', {
            type: 'info',
            icon: 'fa fa-download me-1',
            message: `Generating ${format.toUpperCase()} report download...`
        });

        $('#hub-export-form').submit();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // =============================================================
    // 5. DEEP-LINK / AUTO-SELECT INITIAL TEMPLATE IF PASSED
    // =============================================================
    @if(isset($initialTemplate) && $initialTemplate)
        const initTpl = @json($initialTemplate);
        if (initTpl && initTpl.id) {
            $(`#card-tpl-${initTpl.id} .btn-run-card`).click();
        }
    @endif

});
</script>
@endsection
