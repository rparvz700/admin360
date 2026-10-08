@extends('Partials.app', ['activeMenu' => 'reports-hub'])

@section('title')
    Reports Hub - Published & Saved Reports
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.min.css') }}">
<style>
    /* ============================================================
       REPORTS HUB STYLING (ENTERPRISE ONEUI / BOOTSTRAP 5)
       ============================================================ */
    .hub-header-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 10px;
        color: #ffffff;
        padding: 22px 26px;
        margin-bottom: 22px;
        box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.12);
    }
    .hub-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 18px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .hub-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .report-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e2e8f0;
        color: #334155;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .table-catalog-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .table-catalog-row:hover {
        background-color: #f8fafc !important;
    }
    .table-catalog-row.is-active {
        background-color: #eff6ff !important;
        border-left: 4px solid #3b82f6;
    }
    .report-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .col-pill {
        display: inline-block;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 500;
        border-radius: 4px;
        background-color: #f1f5f9;
        color: #475569;
        margin: 1px 2px 1px 0;
        border: 1px solid #e2e8f0;
    }
    .table-pill {
        display: inline-block;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-radius: 4px;
        background-color: #eff6ff;
        color: #2563eb;
        margin: 1px 2px 1px 0;
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
        scroll-margin-top: 75px;
    }

    /* Compact Horizontal Card View (Alternative to table) */
    .compact-report-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 18px;
        transition: all 0.2s ease;
        border-left: 4px solid #94a3b8;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .compact-report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.06);
        border-color: #3b82f6;
    }
    .compact-report-card.is-active {
        border-left-color: #3b82f6;
        background-color: #f8fafc;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    /* Modern DataTables Processing Indicator */
    .dataTables_processing {
        position: absolute;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        background: rgba(255, 255, 255, 0.96) !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 14px 28px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
        z-index: 1050 !important;
        color: #0f172a !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
    }
    #runner-preview-table tfoot th {
        font-weight: 700;
        background-color: #f8fafc;
        border-top: 2px solid #cbd5e1;
        font-size: 0.8125rem;
    }
    .sql-code-box {
        background-color: #0f172a;
        color: #38bdf8;
        border-radius: 6px;
        padding: 12px 16px;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11px;
        max-height: 180px;
        overflow-y: auto;
        white-space: pre-wrap;
    }
</style>
@endsection

@section('content')
<div class="content">

    <!-- Hero / Header Banner -->
    <div class="hub-header-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-uppercase font-monospace" style="font-size: 10px; letter-spacing: 0.5px;">Analytics & Intelligence</span>
                <span class="text-white-50 fs-xs"><i class="fa fa-shield-halved me-1"></i> Authorized Corporate Reports</span>
            </div>
            <h1 class="h3 fw-bold text-white mb-1">
                <i class="fa fa-table-list text-primary-light me-2"></i> Reports Hub
            </h1>
            <p class="text-white-75 mb-0 fs-sm" style="max-width: 680px;">
                Browse standard published reports, filter with runtime date ranges, view full database records with server-side pagination, and export directly to Excel or CSV.
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
                    <div class="fs-xs fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px;">High-Performance Engine</div>
                    <div class="d-flex gap-2 mt-1">
                        <span class="badge bg-success-light text-success font-monospace fs-xs">Server-Side Pagination</span>
                        <span class="badge bg-secondary-light text-secondary font-monospace fs-xs">Streamed Export</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- REPORT CATALOG: ENTERPRISE TABLE & VIEW SWITCHER        -->
    <!-- ======================================================== -->
    <div class="block block-rounded block-bordered mb-4 shadow-sm">
        <div class="block-header block-header-default border-bottom py-2 px-3">
            <div class="d-flex align-items-center gap-2">
                <h3 class="block-title fw-bold fs-sm mb-0">
                    <i class="fa fa-folder-tree text-primary me-1"></i> Available Report Templates
                </h3>
                <span class="badge bg-secondary fs-xs" id="catalog-count-badge">{{ $templates->count() }} Total</span>
            </div>

            <!-- Toolbar: Filter Pills, Search & View Switcher -->
            <div class="block-options d-flex align-items-center gap-2 flex-wrap">
                <!-- Filter Pills -->
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-xs btn-alt-secondary active tab-filter-btn" data-filter="all">
                        All <span class="badge bg-secondary ms-1">{{ $templates->count() }}</span>
                    </button>
                    <button type="button" class="btn btn-xs btn-alt-secondary tab-filter-btn" data-filter="public">
                        <i class="fa fa-globe text-success me-1"></i> Public <span class="badge bg-success ms-1">{{ $sharedTemplates->count() }}</span>
                    </button>
                    <button type="button" class="btn btn-xs btn-alt-secondary tab-filter-btn" data-filter="private">
                        <i class="fa fa-lock text-muted me-1"></i> Private <span class="badge bg-primary ms-1">{{ $myTemplates->count() }}</span>
                    </button>
                </div>

                <!-- Live Search Box -->
                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text bg-white border-end-0 text-muted py-0">
                        <i class="fa fa-search fs-xs"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0 py-1 fs-xs"
                           id="hub-search-input"
                           placeholder="Filter catalog...">
                </div>

                <!-- View Switcher -->
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-xs btn-alt-secondary active btn-view-mode" data-mode="table" title="Table View">
                        <i class="fa fa-table-list"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-alt-secondary btn-view-mode" data-mode="grid" title="Cards View">
                        <i class="fa fa-grip"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="block-content p-0">
            <!-- 1. PRIMARY VIEW: CORPORATE CATALOG TABLE -->
            <div class="table-responsive" id="catalog-table-view">
                <table class="table table-hover table-vcenter align-middle fs-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 260px;">Report Name & Scope</th>
                            <th style="width: 130px;">Referenced Tables</th>
                            <th style="width: 100px;" class="text-center">Dimensions</th>
                            <th style="width: 110px;" class="text-center">Visibility</th>
                            <th style="width: 150px;">Author</th>
                            <th style="width: 120px;">Last Updated</th>
                            <th style="width: 160px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="catalog-tbody">
                        @forelse($templates as $tpl)
                            @php
                                $isShared = (bool)$tpl->is_shared;
                                $tables = $tpl->tables_summary ?? [];
                                $columns = $tpl->columns_summary ?? [];
                                $creatorName = $tpl->creator->name ?? 'System Admin';
                                $creatorInitials = strtoupper(substr($creatorName, 0, 2));
                                $canEdit = ($tpl->created_by === auth()->id() || auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage-report-templates'));
                            @endphp
                            <tr class="table-catalog-row template-item-node"
                                id="row-tpl-{{ $tpl->id }}"
                                data-id="{{ $tpl->id }}"
                                data-visibility="{{ $isShared ? 'public' : 'private' }}"
                                data-search="{{ strtolower($tpl->name . ' ' . $tpl->description . ' ' . $creatorName . ' ' . implode(' ', $tables) . ' ' . implode(' ', $columns)) }}">
                                <!-- Report Title & Description -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="report-icon-box {{ $isShared ? 'bg-success-light text-success' : 'bg-primary-light text-primary' }}">
                                            <i class="fa {{ $isShared ? 'fa-chart-pie' : 'fa-folder-closed' }}"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" class="fw-bold text-dark fs-sm btn-run-tpl" data-id="{{ $tpl->id }}" data-template='@json($tpl)'>
                                                {{ $tpl->name }}
                                            </a>
                                            <div class="text-muted fs-xs text-truncate" style="max-width: 320px;" title="{{ $tpl->description }}">
                                                {{ $tpl->description ?: 'Standard custom report query.' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Referenced Tables -->
                                <td>
                                    @forelse(array_slice($tables, 0, 2) as $tbl)
                                        <span class="table-pill">{{ $tbl }}</span>
                                    @empty
                                        <span class="text-muted fs-xs">—</span>
                                    @endforelse
                                    @if(count($tables) > 2)
                                        <span class="badge bg-light text-muted fs-xs">+{{ count($tables) - 2 }}</span>
                                    @endif
                                </td>

                                <!-- Columns Count -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border font-monospace" title="{{ implode(', ', $columns) }}">
                                        {{ count($columns) }} Cols
                                    </span>
                                </td>

                                <!-- Visibility -->
                                <td class="text-center">
                                    @if($isShared)
                                        <span class="badge bg-success-light text-success">
                                            <i class="fa fa-globe me-1"></i> Public
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-light text-secondary">
                                            <i class="fa fa-lock me-1"></i> Private
                                        </span>
                                    @endif
                                </td>

                                <!-- Author -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="report-avatar">{{ $creatorInitials }}</span>
                                        <span class="fw-semibold text-truncate" style="max-width: 100px;">{{ $creatorName }}</span>
                                    </div>
                                </td>

                                <!-- Updated At -->
                                <td class="text-muted fs-xs" title="{{ $tpl->updated_at }}">
                                    <div>{{ $tpl->updated_at->format('M d, Y') }}</div>
                                    <div class="fs-xs opacity-75">{{ $tpl->updated_at->diffForHumans() }}</div>
                                </td>

                                <!-- Actions -->
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-xs btn-primary btn-run-tpl" data-id="{{ $tpl->id }}" data-template='@json($tpl)'>
                                            <i class="fa fa-play me-1"></i> Run
                                        </button>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-xs btn-alt-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Export directly">
                                                <i class="fa fa-download"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end fs-xs shadow">
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
                                </td>
                            </tr>
                        @empty
                            <tr id="table-no-data-row">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa fa-folder-open fa-2x mb-2 d-block"></i>
                                    No published templates available. Create reports in Report Builder Studio.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 2. OPTIONAL COMPACT CARDS VIEW (Toggleable) -->
            <div class="p-3 d-none" id="catalog-grid-view">
                <div class="row g-3">
                    @foreach($templates as $tpl)
                        @php
                            $isShared = (bool)$tpl->is_shared;
                            $tables = $tpl->tables_summary ?? [];
                            $columns = $tpl->columns_summary ?? [];
                            $creatorName = $tpl->creator->name ?? 'System Admin';
                            $canEdit = ($tpl->created_by === auth()->id() || auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage-report-templates'));
                        @endphp
                        <div class="col-md-6 col-xl-4 template-item-node"
                             data-id="{{ $tpl->id }}"
                             data-visibility="{{ $isShared ? 'public' : 'private' }}"
                             data-search="{{ strtolower($tpl->name . ' ' . $tpl->description . ' ' . $creatorName . ' ' . implode(' ', $tables) . ' ' . implode(' ', $columns)) }}">
                            <div class="compact-report-card" id="card-tpl-{{ $tpl->id }}">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge {{ $isShared ? 'bg-success-light text-success' : 'bg-primary-light text-primary' }} fs-xs">
                                            <i class="fa {{ $isShared ? 'fa-globe' : 'fa-lock' }} me-1"></i> {{ $isShared ? 'Public' : 'Private' }}
                                        </span>
                                        <span class="fs-xs text-muted">{{ $tpl->updated_at->format('M d, Y') }}</span>
                                    </div>
                                    <h5 class="fw-bold fs-sm mb-1 text-dark text-truncate" title="{{ $tpl->name }}">
                                        {{ $tpl->name }}
                                    </h5>
                                    <p class="fs-xs text-muted mb-2" style="min-height: 32px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $tpl->description ?: 'Standard custom report query.' }}
                                    </p>
                                    <div class="mb-3">
                                        @foreach(array_slice($tables, 0, 2) as $tbl)
                                            <span class="table-pill">{{ $tbl }}</span>
                                        @endforeach
                                        <span class="badge bg-light text-dark border font-monospace fs-xs">{{ count($columns) }} cols</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <span class="fs-xs text-muted"><i class="fa fa-user me-1"></i>{{ $creatorName }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-xs btn-primary btn-run-tpl" data-id="{{ $tpl->id }}" data-template='@json($tpl)'>
                                            <i class="fa fa-play me-1"></i> Run
                                        </button>
                                        <button type="button" class="btn btn-xs btn-alt-secondary btn-direct-export" data-id="{{ $tpl->id }}" data-format="excel" title="Download Excel">
                                            <i class="fa fa-file-excel text-success"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Search Empty Notice -->
            <div id="search-empty-state" class="text-center py-4 d-none">
                <i class="fa fa-search fa-2x text-muted mb-2"></i>
                <h6 class="fw-semibold mb-1">No matching templates found</h6>
                <p class="text-muted fs-xs mb-0">Try clearing the search filter or switching to All Reports.</p>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- INTERACTIVE REPORT RUNNER & SERVER-SIDE DATA TABLE      -->
    <!-- ======================================================== -->
    <div class="runner-container" id="report-runner-section">
        <!-- Standby Hero Banner (Shown when no report is active) -->
        <div id="runner-placeholder" class="block block-rounded block-bordered border-dashed bg-light text-center py-5 shadow-sm">
            <div class="block-content">
                <div class="mb-3 text-primary opacity-50">
                    <i class="fa fa-chart-column fa-3x"></i>
                </div>
                <h4 class="h5 fw-bold text-dark mb-1">Select a Report to Execute</h4>
                <p class="text-muted fs-sm mb-0" style="max-width: 520px; margin: 0 auto;">
                    Click <strong>Run</strong> on any report template above. Data will be fetched via high-performance <strong>Server-Side Pagination</strong> with full sorting, live database search, and instant Excel / CSV export.
                </p>
            </div>
        </div>

        <!-- Active Report Viewer (Server-Side DataTables Engine) -->
        <div id="runner-active-panel" class="block block-rounded block-bordered d-none shadow-sm">
            <!-- Runner Header -->
            <div class="block-header block-header-default border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="report-icon-box bg-primary-light text-primary" style="width: 36px; height: 36px;">
                        <i class="fa fa-table"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="block-title fw-bold fs-base mb-0" id="runner-report-title">
                                Report Name
                            </h3>
                            <span class="badge bg-primary-light text-primary fs-xs font-monospace" id="runner-report-badge">
                                Active Report
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
                    <button type="button" class="btn btn-sm btn-alt-secondary" id="btn-toggle-sql" title="Inspect Generated Query">
                        <i class="fa fa-code me-1"></i> SQL
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-runner-export-excel" title="Download Complete Excel (.xlsx)">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-alt-secondary" id="btn-runner-export-csv" title="Download Complete CSV (.csv)">
                        <i class="fa fa-file-csv me-1"></i> CSV
                    </button>
                    @canany(['manage-report-templates'])
                        <a href="javascript:void(0)" class="btn btn-sm btn-alt-secondary" id="btn-runner-open-builder" title="Open this report in Studio">
                            <i class="fa fa-sliders me-1"></i> Studio
                        </a>
                    @endcanany
                </div>
            </div>

            <!-- Runtime Date Range Filter Toolbar -->
            <div class="block-content bg-light border-bottom py-2 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div id="date-filter-inputs-group" class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fs-xs fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                            <i class="fa fa-calendar me-1"></i> Date Filter:
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

                    <div id="date-filter-unavailable-notice" class="d-none text-muted fs-xs py-1">
                        <i class="fa fa-info-circle text-info me-1"></i> This report template operates on static dimensions without a date filter.
                    </div>

                    <!-- Quick Preset Badges -->
                    <div class="d-flex align-items-center gap-1" id="date-presets-wrapper">
                        <span class="date-preset-pill" data-preset="today">Today</span>
                        <span class="date-preset-pill" data-preset="this_month">This Month</span>
                        <span class="date-preset-pill" data-preset="last_month">Last Month</span>
                        <span class="date-preset-pill" data-preset="this_year">This Year</span>
                        <span class="date-preset-pill active" data-preset="all">All Dates</span>
                    </div>
                </div>
            </div>

            <!-- Server-Side DataTables Content -->
            <div class="block-content p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-vcenter align-middle fs-sm w-100" id="runner-preview-table">
                        <!-- Dynamic Columns generated by DataTables -->
                    </table>
                </div>
            </div>

            <!-- Expandable SQL Debug Drawer -->
            <div class="block-content border-top bg-light p-3 d-none" id="runner-sql-drawer">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-xs fw-bold text-uppercase text-muted"><i class="fa fa-terminal text-primary me-1"></i> Executed PostgreSQL Query</span>
                    <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-copy-sql">
                        <i class="fa fa-copy me-1"></i> Copy SQL
                    </button>
                </div>
                <div class="sql-code-box" id="runner-sql-code">SELECT ...</div>
            </div>

            <!-- Runner Footer Info -->
            <div class="block-content block-content-full block-content-sm bg-body-light fs-xs text-muted d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa fa-bolt text-warning me-1"></i> Powered by Server-Side Query Execution. Use pagination controls above to navigate full dataset without memory limits.
                </div>
                <div>
                    <span class="badge bg-light text-muted font-monospace border" id="runner-base-table-badge">Base: -</span>
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
    <input type="hidden" name="search_value" id="hub-export-search-val" value="">
    <input type="hidden" name="export_sort_column" id="hub-export-sort-col" value="">
    <input type="hidden" name="export_sort_direction" id="hub-export-sort-dir" value="">
</form>
@endsection

@section('scripts')
<!-- DataTables Core & BS5 Integration -->
<script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<!-- Flatpickr, Select2 & SweetAlert2 -->
<script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

<script>
$(document).ready(function() {

    // CSRF Ajax Setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Friendly Alert / Toast Helper
    function showNotify(type, title, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire(title, message, type);
        } else if (typeof One !== 'undefined' && One.helpers) {
            One.helpers('jq-notify', {
                type: type === 'error' ? 'danger' : type,
                icon: type === 'error' ? 'fa fa-exclamation-triangle me-1' : 'fa fa-check me-1',
                message: `${title}: ${message}`
            });
        } else {
            alert(`${title}: ${message}`);
        }
    }

    // Local Date Formatter (avoids UTC timezone drift)
    function formatLocalDate(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // OneUI DataTables Bootstrap 5 Defaults
    jQuery.extend(jQuery.fn.DataTable.ext.classes, {
        sWrapper: "dataTables_wrapper dt-bootstrap5",
        sFilterInput: "form-control form-control-sm",
        sLengthSelect: "form-select form-select-sm"
    });

    jQuery.extend(true, jQuery.fn.DataTable.defaults, {
        language: {
            lengthMenu: "_MENU_",
            search: "_INPUT_",
            searchPlaceholder: "Search records in database...",
            info: "Showing <strong>_START_</strong> to <strong>_END_</strong> of <strong>_TOTAL_</strong> records",
            processing: '<i class="fa fa-spinner fa-spin text-primary me-2"></i> Querying PostgreSQL database...',
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            },
        },
    });

    // Initialize Flatpickr inputs
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.js-hub-flatpickr', {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    }

    // Active Template & Runner DataTable Instance
    let activeTemplate = null;
    let runnerDataTable = null;

    // =============================================================
    // 1. VIEW SWITCHER (TABLE VS CARDS) & FILTERING
    // =============================================================
    $('.btn-view-mode').on('click', function() {
        $('.btn-view-mode').removeClass('active');
        $(this).addClass('active');

        const mode = $(this).data('mode');
        if (mode === 'grid') {
            $('#catalog-table-view').addClass('d-none');
            $('#catalog-grid-view').removeClass('d-none');
        } else {
            $('#catalog-grid-view').addClass('d-none');
            $('#catalog-table-view').removeClass('d-none');
        }
    });

    $('.tab-filter-btn').on('click', function() {
        $('.tab-filter-btn').removeClass('active');
        $(this).addClass('active');
        applyCatalogFilters();
    });

    $('#hub-search-input').on('input', function() {
        applyCatalogFilters();
    });

    function applyCatalogFilters() {
        const filter = $('.tab-filter-btn.active').data('filter');
        const query = $('#hub-search-input').val().toLowerCase().trim();

        let visibleCount = 0;

        $('.template-item-node').each(function() {
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

        $('#catalog-count-badge').text(`${visibleCount} Total`);

        if (visibleCount === 0) {
            $('#search-empty-state').removeClass('d-none');
        } else {
            $('#search-empty-state').addClass('d-none');
        }
    }

    // =============================================================
    // 2. RUN REPORT ACTION (TABLE ROW OR BUTTON CLICK)
    // =============================================================
    $(document).on('click', '.btn-run-tpl', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const tpl = $(this).data('template');
        selectAndRunTemplate(tpl);
    });

    $(document).on('click', '.table-catalog-row', function(e) {
        // If clicking a dropdown button or link, don't trigger row run
        if ($(e.target).closest('.dropdown, .btn-group, a').length) {
            return;
        }

        const tpl = $(this).find('.btn-run-tpl').data('template');
        if (tpl) {
            selectAndRunTemplate(tpl);
        }
    });

    function selectAndRunTemplate(tpl) {
        activeTemplate = tpl;

        // Highlight active row & card
        $('.table-catalog-row').removeClass('is-active');
        $(`#row-tpl-${tpl.id}`).addClass('is-active');
        $('.compact-report-card').removeClass('is-active');
        $(`#card-tpl-${tpl.id}`).addClass('is-active');

        // Reveal active runner panel
        $('#runner-placeholder').addClass('d-none');
        $('#runner-active-panel').removeClass('d-none');

        // Populate Runner Card Header
        $('#runner-report-title').text(tpl.name);
        $('#runner-report-desc').text(tpl.description || 'Pre-configured standard reporting query.');
        $('#runner-report-badge').text(tpl.is_shared ? 'Public Report' : 'Private Report');

        // Studio Link for Admins
        const studioBtn = $('#btn-runner-open-builder');
        if (studioBtn.length) {
            studioBtn.attr('href', `{{ url('reports/builder') }}?template=${tpl.id}`);
        }

        // Configure Date Dropdown Options
        setupDateColumnOptions(tpl);

        // Scroll to runner smoothly
        const runnerEl = document.getElementById('report-runner-section');
        if (runnerEl) {
            runnerEl.scrollIntoView({ behavior: 'smooth' });
        }

        // Build and initialize Server-Side DataTables
        initServerSideReportTable(tpl);
    }

    function setupDateColumnOptions(tpl) {
        const dateFields = tpl.date_columns_summary || [];
        const select = $('#runner-date-col-select');
        select.empty();

        if (dateFields.length > 1) {
            $('#date-col-wrapper').removeClass('d-none');
            $('#date-filter-inputs-group').removeClass('d-none');
            $('#date-presets-wrapper').removeClass('d-none');
            $('#date-filter-unavailable-notice').addClass('d-none');

            dateFields.forEach((df, i) => {
                const optVal = `${df.table}.${df.column}`;
                select.append(`<option value="${optVal}" ${i === 0 ? 'selected' : ''}>Filter On: ${df.label || df.column}</option>`);
            });
        } else if (dateFields.length === 1) {
            $('#date-col-wrapper').addClass('d-none');
            $('#date-filter-inputs-group').removeClass('d-none');
            $('#date-presets-wrapper').removeClass('d-none');
            $('#date-filter-unavailable-notice').addClass('d-none');

            select.append(`<option value="${dateFields[0].table}.${dateFields[0].column}" selected></option>`);
        } else {
            $('#date-col-wrapper').addClass('d-none');
            $('#date-filter-inputs-group').addClass('d-none');
            $('#date-presets-wrapper').addClass('d-none');
            $('#date-filter-unavailable-notice').removeClass('d-none');
        }
    }

    // =============================================================
    // 3. SERVER-SIDE DATATABLES INITIALIZATION & RENDERING
    // =============================================================
    function initServerSideReportTable(tpl) {
        if (!tpl || !tpl.query_config) return;

        // Destroy existing instance and clear table DOM
        if (runnerDataTable) {
            runnerDataTable.destroy();
            $('#runner-preview-table').empty();
        }

        const fields = tpl.query_config.selected_fields || [];
        if (fields.length === 0) {
            $('#runner-preview-table').html('<tr><td class="text-center py-4 text-muted">No fields defined in this report template.</td></tr>');
            return;
        }

        // Define dynamic columns for DataTables
        const dtColumns = [
            {
                data: null,
                title: "#",
                orderable: false,
                searchable: false,
                className: "text-center text-muted fs-xs",
                width: "45px",
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }
        ];

        // Prepare table HTML with thead and tfoot
        let theadHtml = '<thead><tr><th>#</th>';
        let tfootHtml = '<tfoot class="table-light"><tr><th></th>';

        fields.forEach((f, idx) => {
            const isNumeric = f.data_type === 'decimal' || f.data_type === 'integer' || (f.aggregate && f.aggregate !== '');
            const aggBadge = f.aggregate ? ` <span class="badge bg-primary-light text-primary fs-xs font-monospace">${f.aggregate.toUpperCase()}</span>` : '';
            const colTitle = `${escapeHtml(f.alias || f.column)}${aggBadge}`;

            theadHtml += `<th>${colTitle}</th>`;
            tfootHtml += `<th class="${isNumeric ? 'text-end font-monospace' : ''}"></th>`;

            dtColumns.push({
                data: `col_${idx}`,
                name: `col_${idx}`,
                title: colTitle,
                orderable: true,
                className: isNumeric ? 'text-end font-monospace' : '',
                render: function(data, type, row) {
                    if (data === null || data === undefined) return '<span class="text-muted">—</span>';
                    if (type === 'display') {
                        if (isNumeric && !isNaN(data)) {
                            if (f.data_type === 'decimal' || ['sum', 'avg'].includes(f.aggregate)) {
                                return Number(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            return Number(data).toLocaleString();
                        }
                    }
                    return escapeHtml(String(data));
                }
            });
        });

        theadHtml += '</tr></thead>';
        tfootHtml += '</tr></tfoot>';

        $('#runner-preview-table').html(theadHtml + tfootHtml);

        // Initialize Server-Side DataTables
        runnerDataTable = $('#runner-preview-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            responsive: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            dom: "<'row g-2 mb-2 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row g-2 mt-2 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: "{{ route('reports.preview') }}",
                type: "POST",
                data: function(d) {
                    d.template_id = activeTemplate.id;
                    d.runtime_date_from = $('#runner-date-from').val().trim();
                    d.runtime_date_to = $('#runner-date-to').val().trim();
                    d.runtime_date_column = $('#runner-date-col-select').val() || '';
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error occurred while executing report.';
                    showNotify('error', 'Query Execution Error', msg);
                }
            },
            columns: dtColumns,
            footerCallback: function(row, data, start, end, display) {
                const api = this.api();
                fields.forEach((f, idx) => {
                    const colIdx = idx + 1; // +1 because col 0 is '#'
                    const isNumeric = f.data_type === 'decimal' || f.data_type === 'integer' || (f.aggregate && f.aggregate !== '');

                    if (isNumeric) {
                        let total = 0;
                        let validCount = 0;
                        api.column(colIdx, { page: 'current' }).data().each(function(val) {
                            const num = parseFloat(val);
                            if (!isNaN(num)) {
                                total += num;
                                validCount++;
                            }
                        });

                        if (validCount > 0) {
                            const formatted = (f.data_type === 'decimal' || ['sum', 'avg'].includes(f.aggregate))
                                ? total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                                : total.toLocaleString();
                            $(api.column(colIdx).footer()).html(`<strong>${formatted}</strong>`);
                        } else {
                            $(api.column(colIdx).footer()).html('');
                        }
                    } else {
                        if (idx === 0) {
                            $(api.column(colIdx).footer()).html('<strong>Page Total:</strong>');
                        } else {
                            $(api.column(colIdx).footer()).html('');
                        }
                    }
                });
            },
            drawCallback: function(settings) {
                if (settings.json) {
                    $('#runner-base-table-badge').text(`Base Table: ${settings.json.base_table || 'unknown'}`);
                    if (settings.json.sql) {
                        $('#runner-sql-code').text(settings.json.sql);
                    }
                }
            }
        });
    }

    // Re-run Button
    $('#btn-runner-refresh').on('click', function() {
        if (runnerDataTable) {
            runnerDataTable.ajax.reload(null, false);
        }
    });

    // SQL Toggle Button
    $('#btn-toggle-sql').on('click', function() {
        $('#runner-sql-drawer').toggleClass('d-none');
    });

    // Copy SQL Button
    $('#btn-copy-sql').on('click', function() {
        const sql = $('#runner-sql-code').text();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(sql).then(() => {
                showNotify('success', 'Copied', 'SQL query copied to clipboard.');
            }).catch(() => {
                prompt('Copy SQL query:', sql);
            });
        } else {
            prompt('Copy SQL query:', sql);
        }
    });

    // Apply Date Range
    $('#btn-apply-date-filter').on('click', function() {
        if (runnerDataTable) {
            runnerDataTable.ajax.reload();
        }
    });

    $('#btn-clear-date-filter').on('click', function() {
        $('#runner-date-from').val('');
        $('#runner-date-to').val('');
        $('.date-preset-pill').removeClass('active');
        $('.date-preset-pill[data-preset="all"]').addClass('active');
        if (runnerDataTable) {
            runnerDataTable.ajax.reload();
        }
    });

    // Quick Date Presets Click (Timezone Safe)
    $('.date-preset-pill').on('click', function() {
        $('.date-preset-pill').removeClass('active');
        $(this).addClass('active');

        const preset = $(this).data('preset');
        const now = new Date();
        const fromInput = $('#runner-date-from');
        const toInput = $('#runner-date-to');

        if (preset === 'today') {
            const todayStr = formatLocalDate(now);
            fromInput.val(todayStr);
            toInput.val(todayStr);
        } else if (preset === 'this_month') {
            const y = now.getFullYear();
            const m = now.getMonth();
            const firstDay = new Date(y, m, 1);
            const lastDay = new Date(y, m + 1, 0);
            fromInput.val(formatLocalDate(firstDay));
            toInput.val(formatLocalDate(lastDay));
        } else if (preset === 'last_month') {
            const y = now.getFullYear();
            const m = now.getMonth() - 1;
            const firstDay = new Date(y, m, 1);
            const lastDay = new Date(y, m + 1, 0);
            fromInput.val(formatLocalDate(firstDay));
            toInput.val(formatLocalDate(lastDay));
        } else if (preset === 'this_year') {
            const y = now.getFullYear();
            fromInput.val(`${y}-01-01`);
            toInput.val(`${y}-12-31`);
        } else if (preset === 'all') {
            fromInput.val('');
            toInput.val('');
        }

        if (runnerDataTable) {
            runnerDataTable.ajax.reload();
        }
    });

    // =============================================================
    // 4. EXPORT HANDLERS (EXCEL / CSV WITH RUNTIME DATE & SEARCH FILTERS)
    // =============================================================
    function getRunnerExportContext() {
        let searchValue = '';
        let sortCol = '';
        let sortDir = '';
        if (runnerDataTable) {
            searchValue = runnerDataTable.search() || '';
            const order = runnerDataTable.order();
            if (order && order.length > 0) {
                sortCol = order[0][0]; // column index
                sortDir = order[0][1]; // 'asc' or 'desc'
            }
        }
        return { searchValue, sortCol, sortDir };
    }

    // Direct Export from Catalog Rows
    $(document).on('click', '.btn-direct-export', function(e) {
        e.preventDefault();
        e.stopPropagation();
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
        const ctx = getRunnerExportContext();
        triggerExport(activeTemplate.id, 'excel', dateFrom, dateTo, dateCol, ctx.searchValue, ctx.sortCol, ctx.sortDir);
    });

    $('#btn-runner-export-csv').on('click', function() {
        if (!activeTemplate) return;
        const dateFrom = $('#runner-date-from').val().trim();
        const dateTo   = $('#runner-date-to').val().trim();
        const dateCol  = $('#runner-date-col-select').val() || '';
        const ctx = getRunnerExportContext();
        triggerExport(activeTemplate.id, 'csv', dateFrom, dateTo, dateCol, ctx.searchValue, ctx.sortCol, ctx.sortDir);
    });

    function triggerExport(templateId, format, dateFrom = '', dateTo = '', dateCol = '', searchVal = '', sortCol = '', sortDir = '') {
        $('#hub-export-template-id').val(templateId);
        $('#hub-export-format').val(format);
        $('#hub-export-date-from').val(dateFrom);
        $('#hub-export-date-to').val(dateTo);
        $('#hub-export-date-col').val(dateCol);
        $('#hub-export-search-val').val(searchVal);
        $('#hub-export-sort-col').val(sortCol);
        $('#hub-export-sort-dir').val(sortDir);

        if (typeof One !== 'undefined' && One.helpers) {
            One.helpers('jq-notify', {
                type: 'info',
                icon: 'fa fa-download me-1',
                message: `Generating ${format.toUpperCase()} report download...`
            });
        }

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
            selectAndRunTemplate(initTpl);
        }
    @endif

});
</script>
@endsection
