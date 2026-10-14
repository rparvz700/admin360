@extends('Partials.app', ['activeMenu' => 'report-builder'])

@section('title')
    Custom Report Builder Studio
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.min.css') }}">
<style>
    /* ============================================================
       TOP-NOTCH REPORT BUILDER STUDIO STYLING
       ============================================================ */
    /* Select2 Studio Theme Integration */
    .select2-container--default .select2-selection--single {
        height: 31px;
        padding: 2px 6px;
        font-size: 0.8125rem;
        border-color: #cbd5e1;
        border-radius: 0.25rem;
        background-color: #ffffff;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 25px;
        color: #1e293b;
        padding-left: 4px;
        font-size: 0.8125rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
    .select2-container--default .select2-dropdown {
        border-color: #cbd5e1;
        font-size: 0.8125rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        z-index: 1060;
    }
    .select2-container--default .select2-results__group {
        padding: 5px 8px;
        font-weight: 700;
        color: #475569;
        background-color: #f1f5f9;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .select2-container--default .select2-results__option {
        padding: 5px 8px;
        font-size: 12px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-color: #cbd5e1;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
    }
    .filter-condition-row .select2-container,
    .sort-row .select2-container,
    .selected-column-card .select2-container {
        width: 100% !important;
    }
    :root {
        --studio-sidebar-width: 380px;
        --dimension-border: #3b82f6;
        --dimension-bg: #eff6ff;
        --dimension-text: #1d4ed8;
        --metric-border: #10b981;
        --metric-bg: #ecfdf5;
        --metric-text: #047857;
    }

    /* Studio Layout */
    .studio-container {
        display: flex;
        gap: 20px;
        transition: all 0.3s ease;
        position: relative;
    }
    .studio-sidebar {
        width: var(--studio-sidebar-width);
        flex-shrink: 0;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .studio-sidebar.collapsed {
        margin-left: calc(-1 * var(--studio-sidebar-width) - 20px);
        opacity: 0;
        pointer-events: none;
    }
    .studio-workspace {
        flex-grow: 1;
        min-width: 0;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Top Studio Bar */
    .studio-top-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .report-title-input {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 6px;
        padding: 2px 8px;
        transition: all 0.15s ease;
        max-width: 320px;
    }
    .report-title-input:hover, .report-title-input:focus {
        border-color: #cbd5e1;
        background: #f8fafc;
        outline: none;
    }

    /* Type Filter Pills */
    .type-pill {
        cursor: pointer;
        padding: 3px 9px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        transition: all 0.15s;
        user-select: none;
    }
    .type-pill:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .type-pill.active {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    /* Dictionary Tree Items */
    .dictionary-column-item {
        cursor: grab;
        transition: all 0.15s ease;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #ffffff;
        padding: 7px 10px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dictionary-column-item:hover {
        background-color: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .dictionary-column-item:active {
        cursor: grabbing;
    }
    .dictionary-column-item.pulse-added {
        animation: pulseAdded 0.6s ease;
    }
    @keyframes pulseAdded {
        0% { transform: scale(1); background-color: #dbeafe; }
        50% { transform: scale(1.03); background-color: #93c5fd; }
        100% { transform: scale(1); background-color: #ffffff; }
    }

    /* Search Highlighting */
    .search-highlight {
        background: #fef08a;
        font-weight: bold;
        border-radius: 2px;
        padding: 0 2px;
    }

    /* Drop Zone */
    .drop-zone {
        min-height: 120px;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background-color: #f8fafc;
        padding: 12px;
        transition: all 0.2s ease;
    }
    .drop-zone.dragover {
        background-color: #e0f2fe;
        border-color: #0284c7;
        border-style: solid;
    }

    /* Selected Column Cards (Dimension vs Metric) */
    .selected-column-card {
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.15s ease;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .selected-column-card.is-metric {
        border-left: 4px solid var(--metric-border);
        background: #fafdfc;
    }
    .selected-column-card.is-dimension {
        border-left: 4px solid var(--dimension-border);
        background: #fcfdfe;
    }
    .selected-column-card.dragging {
        opacity: 0.4;
        border: 2px dashed #64748b;
    }
    .drag-handle {
        cursor: grab;
        color: #94a3b8;
    }
    .drag-handle:active {
        cursor: grabbing;
    }

    /* Filter Tree with Visual Branch Connectors */
    .filter-tree-root {
        position: relative;
    }
    /* Button XS definition */
    .btn-xs {
        padding: 0.22rem 0.55rem !important;
        font-size: 0.75rem !important;
        line-height: 1.25 !important;
        border-radius: 4px !important;
        white-space: nowrap !important;
    }
    .filter-group-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 10px;
        position: relative;
    }
    .filter-group-card.group-and {
        border-left: 4px solid #3b82f6;
    }
    .filter-group-card.group-or {
        border-left: 4px solid #f59e0b;
    }
    .filter-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: nowrap;
    }
    .filter-group-header .btn-toggle-logic {
        white-space: nowrap !important;
        font-weight: 600;
        font-size: 11px !important;
        padding: 3px 8px !important;
    }
    .filter-children-container {
        margin-left: 12px;
        padding-left: 12px;
        border-left: 2px solid #e2e8f0;
        position: relative;
    }
    .filter-condition-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 10px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        position: relative;
        transition: border-color 0.15s;
    }
    .filter-condition-row:hover {
        border-color: #cbd5e1;
    }
    .filter-condition-row::before {
        content: '';
        position: absolute;
        left: -14px;
        top: 50%;
        width: 12px;
        height: 2px;
        background: #e2e8f0;
    }
    .btn-remove-condition {
        width: 30px;
        height: 30px;
        min-width: 30px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        color: #ef4444;
        background: #ffffff;
        border: 1px solid #fee2e2;
        transition: all 0.15s ease;
        flex-shrink: 0;
        cursor: pointer;
    }
    .btn-remove-condition:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }

    /* Quick Date Preset Badges */
    .date-preset-badge {
        font-size: 10px;
        cursor: pointer;
        padding: 2px 7px;
        border-radius: 12px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.15s;
    }
    .date-preset-badge:hover {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #7dd3fc;
    }

    /* SQL Inspector */
    .sql-preview-box {
        background: #0f172a;
        color: #38bdf8;
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 13px;
        padding: 16px;
        border-radius: 8px;
        overflow-x: auto;
        max-height: 380px;
        white-space: pre-wrap;
        line-height: 1.6;
        border: 1px solid #1e293b;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
    }

    /* KPI Summary Cards */
    .kpi-card {
        border-radius: 8px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    /* Sticky Results Table */
    .table-responsive-studio {
        max-height: 560px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .table-sticky-head thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #1e293b !important;
        color: #f8fafc !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }

    /* Fullscreen Mode */
    .studio-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        background: #f8fafc !important;
        padding: 20px !important;
        overflow-y: auto !important;
    }

    /* Badges */
    .badge-datatype {
        font-size: 9px;
        text-transform: uppercase;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .type-decimal, .type-integer { background: #e0f2fe; color: #0369a1; }
    .type-string, .type-text { background: #f3e8ff; color: #7e22ce; }
    .type-date, .type-datetime { background: #fef3c7; color: #b45309; }
    .type-boolean { background: #dcfce7; color: #15803d; }
    .type-json { background: #ffedd5; color: #c2410c; }
</style>
@endsection

@section('content')
<div class="content" id="report-studio-wrapper">

    <!-- ======================================================== -->
    <!-- STUDIO TOP BAR & RUN ACTIONS                            -->
    <!-- ======================================================== -->
    <div class="studio-top-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Title & Status -->
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="btn-toggle-sidebar" title="Toggle Sidebar (Expand/Collapse)">
                <i class="fa fa-bars me-1"></i> <span class="d-none d-sm-inline">Catalog</span>
            </button>
            <input type="text" id="report-title-field" class="report-title-input" value="Untitled Custom Report" title="Click to rename report">
            <span id="unsaved-badge" class="badge bg-warning-light text-warning d-none" title="You have unsaved changes">Unsaved</span>
            <span id="active-template-pill" class="badge bg-primary-light text-primary d-none"></span>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Run Preview Button -->
            <button type="button" class="btn btn-primary px-3 shadow-sm" id="btn-preview-report" title="Shortcut: Ctrl + Enter">
                <i class="fa fa-play me-1"></i> <strong>Preview Report</strong>
            </button>

            <!-- Export Dropdown -->
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle shadow-sm" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="exportDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" id="btn-export-csv">
                            <i class="fa fa-file-csv text-success me-2 fs-lg"></i>
                            <div>
                                <div class="fw-semibold">CSV Streamed (.csv)</div>
                                <div class="text-muted fs-xs">Ultra-fast, zero memory cap</div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" id="btn-export-excel">
                            <i class="fa fa-file-excel text-success me-2 fs-lg"></i>
                            <div>
                                <div class="fw-semibold">Excel Workbook (.xlsx)</div>
                                <div class="text-muted fs-xs">Formatted headers & columns</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Save / Load Template -->
            <button type="button" class="btn btn-alt-primary" id="btn-open-save-modal" title="Shortcut: Ctrl + S">
                <i class="fa fa-floppy-disk me-1"></i> Save
            </button>
            <button type="button" class="btn btn-alt-secondary" data-bs-toggle="modal" data-bs-target="#modal-load-template">
                <i class="fa fa-folder-open me-1"></i> Templates
            </button>
            <a href="{{ route('reports.hub') }}" class="btn btn-alt-secondary" title="Switch to Reports Hub">
                <i class="fa fa-table-list me-1"></i> Hub
            </a>

            <!-- Reset -->
            <button type="button" class="btn btn-sm btn-alt-danger" id="btn-reset-builder" title="Reset everything">
                <i class="fa fa-rotate-left"></i>
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- STUDIO MAIN WORKSPACE (SIDEBAR + CONTENT)                -->
    <!-- ======================================================== -->
    <div class="studio-container">

        <!-- LEFT PANEL: DATA DICTIONARY CATALOG -->
        <div class="studio-sidebar" id="studio-sidebar-panel">
            <div class="block block-rounded block-bordered mb-0 shadow-sm h-100">
                <div class="block-header block-header-default py-2">
                    <h3 class="block-title fs-sm fw-bold">
                        <i class="fa fa-database text-muted me-1"></i> Data Catalog
                    </h3>
                    <div class="block-options">
                        <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-toggle-accordion-all" title="Expand/Collapse All">
                            <i class="fa fa-arrows-up-down"></i>
                        </button>
                        @can('manage-data-dictionary')
                        <button type="button" class="btn btn-xs btn-alt-primary ms-1" id="btn-quick-sync" title="Sync database schema">
                            <i class="fa fa-arrows-rotate"></i>
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="block-content p-3">
                    <!-- Search Input -->
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="field-search-input" class="form-control border-start-0 ps-0" placeholder="Search tables or columns...">
                        <button class="btn btn-outline-secondary border" type="button" id="btn-clear-search" style="display: none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Type Filter Pills -->
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <span class="type-pill active" data-filter="all">All</span>
                        <span class="type-pill" data-filter="numeric">Numbers (#)</span>
                        <span class="type-pill" data-filter="date">Dates (📅)</span>
                        <span class="type-pill" data-filter="string">Text (🔤)</span>
                        <span class="type-pill" data-filter="boolean">Boolean (✓)</span>
                    </div>

                    <!-- Accordion Tree -->
                    <div class="sidebar-tree-container" id="dictionary-tree" style="max-height: 640px; overflow-y: auto;">
                        @forelse($dictionary as $moduleName => $moduleTables)
                            <div class="module-group mb-3" data-module="{{ $moduleName }}">
                                <div class="fw-bold text-uppercase fs-xs text-primary mb-1 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-folder-closed me-1"></i> {{ $moduleName }}</span>
                                    <span class="badge bg-primary-light text-primary rounded-pill">{{ $moduleTables->flatten()->count() }}</span>
                                </div>

                                <div class="accordion accordion-flush" id="acc-{{ Str::slug($moduleName) }}">
                                    @foreach($moduleTables as $tableName => $columns)
                                        <div class="accordion-item border-0 mb-1 table-node" data-table="{{ $tableName }}">
                                            <h2 class="accordion-header" id="heading-{{ Str::slug($tableName) }}">
                                                <button class="accordion-button collapsed py-1 px-2 fs-xs bg-light rounded text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ Str::slug($tableName) }}">
                                                    <i class="fa fa-table text-muted me-2"></i>
                                                    <span class="fw-semibold table-name-label">{{ Str::headline($tableName) }}</span>
                                                    <span class="badge bg-secondary-light text-secondary ms-auto me-2">{{ count($columns) }}</span>
                                                </button>
                                            </h2>
                                            <div id="collapse-{{ Str::slug($tableName) }}" class="accordion-collapse collapse" data-bs-parent="#acc-{{ Str::slug($moduleName) }}">
                                                <div class="accordion-body p-1 pt-2">
                                                    @foreach($columns as $col)
                                                        @php
                                                            $groupType = 'string';
                                                            if (in_array($col->data_type, ['decimal', 'integer'])) $groupType = 'numeric';
                                                            elseif (in_array($col->data_type, ['date', 'datetime'])) $groupType = 'date';
                                                            elseif ($col->data_type === 'boolean') $groupType = 'boolean';
                                                        @endphp
                                                        <div class="dictionary-column-item"
                                                             draggable="true"
                                                             data-table="{{ $col->table_name }}"
                                                             data-column="{{ $col->column_name }}"
                                                             data-label="{{ $col->display_name }}"
                                                             data-type="{{ $col->data_type }}"
                                                             data-group-type="{{ $groupType }}"
                                                             data-aggregatable="{{ $col->is_aggregatable ? '1' : '0' }}"
                                                             data-description="{{ $col->description ?: $col->display_name }}">
                                                            <div class="d-flex align-items-center gap-2 text-truncate">
                                                                <i class="fa fa-grip-vertical text-muted fs-xs drag-handle"></i>
                                                                <div>
                                                                    <div class="fs-xs fw-semibold text-truncate col-display-label" style="max-width: 175px;">
                                                                        {{ $col->display_name }}
                                                                    </div>
                                                                    <div class="text-muted font-monospace col-raw-label" style="font-size: 10px;">
                                                                        {{ $col->column_name }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-1">
                                                                <span class="badge-datatype type-{{ $col->data_type }}">
                                                                    {{ $col->data_type }}
                                                                </span>
                                                                <button type="button" class="btn btn-xs btn-alt-secondary btn-add-column" title="Quick Add (+)">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fa fa-database fa-2x mb-2 text-muted"></i>
                                <p class="fs-sm mb-0">No data catalog records found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: WORKSPACE (TABS: BUILDER / RESULTS / SQL) -->
        <div class="studio-workspace">

            <!-- Workspace Tabs Nav -->
            <ul class="nav nav-tabs nav-tabs-block mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-builder-btn" data-bs-toggle="tab" data-bs-target="#tab-builder" type="button" role="tab">
                        <i class="fa fa-layer-group me-1"></i> <strong>Query Builder</strong>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-results-btn" data-bs-toggle="tab" data-bs-target="#tab-results" type="button" role="tab">
                        <i class="fa fa-table me-1"></i> <strong>Data Preview</strong>
                        <span id="tab-results-badge" class="badge bg-primary ms-1 d-none">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-sql-btn" data-bs-toggle="tab" data-bs-target="#tab-sql" type="button" role="tab">
                        <i class="fa fa-code me-1"></i> <strong>SQL Inspector</strong>
                    </button>
                </li>
                <li class="nav-item ms-auto d-flex align-items-center pe-2">
                    <span class="fs-xs text-muted" id="query-timer-badge"></span>
                </li>
            </ul>

            <!-- Workspace Tab Panes -->
            <div class="tab-content">

                <!-- ============================================== -->
                <!-- TAB 1: QUERY BUILDER (COLUMNS, FILTERS, SORTS)  -->
                <!-- ============================================== -->
                <div class="tab-pane fade show active" id="tab-builder" role="tabpanel">

                    <!-- SECTION 1: Selected Report Columns Dropzone -->
                    <div class="block block-rounded block-bordered mb-4 shadow-sm">
                        <div class="block-header block-header-default py-2">
                            <h3 class="block-title fs-sm fw-bold">
                                <i class="fa fa-table-columns text-primary me-1"></i> Selected Columns & Aggregates
                            </h3>
                            <div class="block-options d-flex align-items-center gap-2">
                                <span class="badge bg-primary" id="selected-columns-counter">0 Columns</span>
                                <button type="button" class="btn btn-xs btn-alt-danger" id="btn-clear-columns" title="Clear all columns">
                                    <i class="fa fa-trash-can me-1"></i> Clear All
                                </button>
                            </div>
                        </div>

                        <div class="block-content p-3">
                            <div id="columns-drop-zone" class="drop-zone">
                                <div id="columns-empty-notice" class="text-center text-muted py-4">
                                    <i class="fa fa-cloud-arrow-down fa-2x text-slate-300 mb-2"></i>
                                    <div class="fw-semibold">Drag & drop fields here to add columns</div>
                                    <div class="fs-xs text-muted">Or click the <strong>(+)</strong> icon on any field from the catalog on the left</div>
                                </div>
                                <div id="selected-columns-container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Filter Criteria Condition Tree -->
                    <div class="block block-rounded block-bordered mb-4 shadow-sm">
                        <div class="block-header block-header-default py-2">
                            <h3 class="block-title fs-sm fw-bold">
                                <i class="fa fa-filter text-primary me-1"></i> Filter Conditions (AND / OR)
                            </h3>
                            <div class="block-options">
                                <button type="button" class="btn btn-xs btn-alt-primary" id="btn-add-root-condition">
                                    <i class="fa fa-plus me-1"></i> Add Condition
                                </button>
                                <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-add-root-group">
                                    <i class="fa fa-diagram-project me-1"></i> Add Group
                                </button>
                            </div>
                        </div>

                        <div class="block-content p-3">
                            <div id="filter-tree-container">
                                <!-- Root group dynamically rendered -->
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Multi-Column Sorting Panel -->
                    <div class="block block-rounded block-bordered mb-4 shadow-sm">
                        <div class="block-header block-header-default py-2">
                            <h3 class="block-title fs-sm fw-bold">
                                <i class="fa fa-arrow-down-short-wide text-primary me-1"></i> Sorting & Ordering
                            </h3>
                            <div class="block-options">
                                <button type="button" class="btn btn-xs btn-alt-primary" id="btn-add-sort">
                                    <i class="fa fa-plus me-1"></i> Add Sort Column
                                </button>
                            </div>
                        </div>
                        <div class="block-content p-3">
                            <div id="sorts-container">
                                <div class="text-muted fs-xs fst-italic text-center py-2" id="sorts-empty-notice">
                                    No sorting rules applied. Default database order will be used.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ============================================== -->
                <!-- TAB 2: DATA PREVIEW (TABLE & KPI SUMMARY)       -->
                <!-- ============================================== -->
                <div class="tab-pane fade" id="tab-results" role="tabpanel">

                    <!-- KPI Metric Cards Bar (Auto-calculated) -->
                    <div class="row g-2 mb-3" id="kpi-summary-cards" style="display: none;">
                        <div class="col-sm-4">
                            <div class="kpi-card border-start border-4 border-primary">
                                <div class="fs-xs fw-bold text-muted text-uppercase">Total Rows</div>
                                <div class="fs-3 fw-bold text-dark" id="kpi-total-rows">0</div>
                                <div class="fs-xs text-muted" id="kpi-preview-rows">Previewing top 50</div>
                            </div>
                        </div>
                        <div class="col-sm-4" id="kpi-sum-card" style="display: none;">
                            <div class="kpi-card border-start border-4 border-success">
                                <div class="fs-xs fw-bold text-muted text-uppercase" id="kpi-sum-label">Aggregate Sum</div>
                                <div class="fs-3 fw-bold text-success font-monospace" id="kpi-sum-val">0.00</div>
                                <div class="fs-xs text-muted">Preview page sum</div>
                            </div>
                        </div>
                        <div class="col-sm-4" id="kpi-avg-card" style="display: none;">
                            <div class="kpi-card border-start border-4 border-info">
                                <div class="fs-xs fw-bold text-muted text-uppercase" id="kpi-avg-label">Average</div>
                                <div class="fs-3 fw-bold text-info font-monospace" id="kpi-avg-val">0.00</div>
                                <div class="fs-xs text-muted">Preview page mean</div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table Block -->
                    <div class="block block-rounded block-bordered shadow-sm" id="results-block">
                        <div class="block-header block-header-default py-2">
                            <h3 class="block-title fs-sm fw-bold">
                                <i class="fa fa-table text-muted me-1"></i> Preview Records
                            </h3>
                            <div class="block-options d-flex align-items-center gap-2">
                                <span class="badge bg-secondary" id="results-meta-badges">Awaiting execution</span>
                                <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-fullscreen-table" title="Toggle Fullscreen">
                                    <i class="fa fa-expand"></i>
                                </button>
                            </div>
                        </div>

                        <!-- In-Table Search Filter Bar -->
                        <div class="block-content bg-light p-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" id="preview-filter-input" class="form-control bg-white" placeholder="Filter preview rows...">
                            </div>
                            <div class="fs-xs text-muted" id="preview-filter-count"></div>
                        </div>

                        <div class="block-content p-0">
                            <div class="table-responsive-studio">
                                <table class="table table-hover table-striped table-sm mb-0 align-middle fs-sm table-sticky-head" id="preview-results-table">
                                    <thead class="table-dark" id="preview-table-head">
                                        <tr>
                                            <th class="text-center py-3 text-muted">No columns selected</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-table-body">
                                        <tr>
                                            <td class="text-center py-5 text-muted">
                                                <i class="fa fa-chart-simple fa-2x mb-2 d-block text-slate-300"></i>
                                                Select columns in the <strong>Query Builder</strong> and click <strong>Preview Report</strong> to load live data.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 3: SQL INSPECTOR                           -->
                <!-- ============================================== -->
                <div class="tab-pane fade" id="tab-sql" role="tabpanel">
                    <div class="block block-rounded block-bordered shadow-sm">
                        <div class="block-header block-header-default py-2">
                            <h3 class="block-title fs-sm fw-bold">
                                <i class="fa fa-terminal text-muted me-1"></i> Parameterized SQL Query
                            </h3>
                            <div class="block-options">
                                <button type="button" class="btn btn-xs btn-alt-secondary" id="btn-copy-sql">
                                    <i class="fa fa-copy me-1"></i> Copy SQL
                                </button>
                            </div>
                        </div>
                        <div class="block-content p-3">
                            <div class="sql-preview-box" id="sql-preview-text">-- Click "Preview Report" to compile and inspect raw SQL query</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL: SAVE REPORT TEMPLATE                             -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-save-template" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-floppy-disk text-primary me-2"></i> Save Report Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="template-id-input" value="">

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold" for="template-name-input">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="template-name-input" placeholder="e.g. Monthly Postpaid Bill Summary">
                </div>

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold" for="template-desc-input">Description (Optional)</label>
                    <textarea class="form-control" id="template-desc-input" rows="2" placeholder="Briefly describe what this report covers..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold d-block">Visibility</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="template-visibility" id="vis-private" value="0" checked>
                        <label class="form-check-label fs-sm" for="vis-private">
                            <i class="fa fa-lock text-muted me-1"></i> Private (Only Me)
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="template-visibility" id="vis-shared" value="1">
                        <label class="form-check-label fs-sm" for="vis-shared">
                            <i class="fa fa-globe text-success me-1"></i> Public / Shared (All Users)
                        </label>
                    </div>
                </div>

                <div id="save-mode-group" class="d-none mb-3">
                    <label class="form-label fs-sm fw-semibold d-block">Save Option</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="save-mode" id="save-mode-update" value="update" checked>
                        <label class="form-check-label fs-sm" for="save-mode-update">
                            Overwrite existing template: <strong id="overwrite-template-name"></strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="save-mode" id="save-mode-new" value="new">
                        <label class="form-check-label fs-sm" for="save-mode-new">
                            Save as a brand new template
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-save-template">
                    <i class="fa fa-check me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: LOAD REPORT TEMPLATE                             -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-load-template" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-folder-open text-primary me-2"></i> Saved Report Templates
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" id="template-search-input" placeholder="Search templates by title or creator...">
                </div>

                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover table-striped align-middle fs-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Template Title</th>
                                <th>Visibility</th>
                                <th>Creator</th>
                                <th>Last Updated</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="template-list-tbody">
                            @forelse($templates as $tpl)
                                <tr class="template-row" data-id="{{ $tpl->id }}" data-name="{{ $tpl->name }}" data-creator="{{ $tpl->creator->name ?? 'System' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $tpl->name }}</div>
                                        @if($tpl->description)
                                            <div class="text-muted fs-xs text-truncate" style="max-width: 280px;">{{ $tpl->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tpl->is_shared)
                                            <span class="badge bg-success-light text-success"><i class="fa fa-globe me-1"></i> Shared</span>
                                        @else
                                            <span class="badge bg-secondary-light text-secondary"><i class="fa fa-lock me-1"></i> Private</span>
                                        @endif
                                    </td>
                                    <td>{{ $tpl->creator->name ?? 'System' }}</td>
                                    <td class="text-muted fs-xs">{{ $tpl->updated_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-xs btn-primary btn-load-tpl" data-template='@json($tpl)'>
                                            <i class="fa fa-upload me-1"></i> Load
                                        </button>
                                        @if($tpl->created_by === auth()->id() || auth()->user()->hasRole('Super Admin'))
                                            <button type="button" class="btn btn-xs btn-alt-danger btn-delete-tpl" data-id="{{ $tpl->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No saved templates available. Create a query and save it!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Export Form -->
<form id="export-form" method="POST" action="{{ route('reports.export') }}" target="_blank" class="d-none">
    @csrf
    <input type="hidden" name="export_format" id="export-format-input" value="csv">
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
$(document).ready(function() {

    // =============================================================
    // GLOBAL REPORT STATE
    // =============================================================
    let state = {
        title: 'Untitled Custom Report',
        selected_fields: [], // [{table, column, alias, aggregate, data_type, display_name, aggregatable}]
        filters: {
            logical_operator: 'AND',
            conditions: []
        },
        sorts: [], // [{table, column, direction}]
        current_template: null,
        is_dirty: false
    };

    let rawPreviewRows = []; // For in-table search filtering

    // Pre-indexed dictionary for O(1) lookups
    const dictionaryLookup = {};
    $('.dictionary-column-item').each(function() {
        const t = $(this).data('table');
        const c = $(this).data('column');
        if (!dictionaryLookup[t]) dictionaryLookup[t] = {};
        dictionaryLookup[t][c] = {
            table: t,
            column: c,
            label: $(this).data('label'),
            type: $(this).data('type'),
            group_type: $(this).data('group-type'),
            aggregatable: $(this).data('aggregatable') == '1',
            description: $(this).data('description')
        };
    });

    // CSRF Ajax Setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // =============================================================
    // 1. STUDIO TITLE & WORKSPACE TOGGLE
    // =============================================================
    $('#report-title-field').on('input', function() {
        state.title = $(this).val().trim();
        markDirty();
    });

    $('#btn-toggle-sidebar').on('click', function() {
        $('#studio-sidebar-panel').toggleClass('collapsed');
    });

    function markDirty() {
        state.is_dirty = true;
        $('#unsaved-badge').removeClass('d-none');
    }

    function markClean() {
        state.is_dirty = false;
        $('#unsaved-badge').addClass('d-none');
    }

    // Keyboard Shortcuts: Ctrl + Enter to run, Ctrl + S to save
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            $('#btn-preview-report').click();
        } else if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            $('#btn-open-save-modal').click();
        }
    });

    // =============================================================
    // 2. DATA DICTIONARY SEARCH & TYPE-FILTER PILLS
    // =============================================================
    $('.type-pill').on('click', function() {
        $('.type-pill').removeClass('active');
        $(this).addClass('active');
        applyCatalogFilters();
    });

    $('#field-search-input').on('input', function() {
        const hasText = $(this).val().length > 0;
        $('#btn-clear-search').toggle(hasText);
        applyCatalogFilters();
    });

    $('#btn-clear-search').on('click', function() {
        $('#field-search-input').val('');
        $(this).hide();
        applyCatalogFilters();
    });

    // Accordion Expand/Collapse All
    let allExpanded = false;
    $('#btn-toggle-accordion-all').on('click', function() {
        allExpanded = !allExpanded;
        if (allExpanded) {
            $('.accordion-collapse').addClass('show');
            $('.accordion-button').removeClass('collapsed');
        } else {
            $('.accordion-collapse').removeClass('show');
            $('.accordion-button').addClass('collapsed');
        }
    });

    function applyCatalogFilters() {
        const query = $('#field-search-input').val().toLowerCase().trim();
        const activeType = $('.type-pill.active').data('filter');

        $('.module-group').each(function() {
            let moduleHasMatch = false;

            $(this).find('.table-node').each(function() {
                const tableName = $(this).data('table').toLowerCase();
                let tableHasMatch = query !== '' && tableName.includes(query);

                $(this).find('.dictionary-column-item').each(function() {
                    const colName = $(this).data('column').toLowerCase();
                    const label   = $(this).data('label').toLowerCase();
                    const groupType = $(this).data('group-type');

                    // Filter match
                    const matchesType = (activeType === 'all') || (groupType === activeType);
                    const matchesQuery = (!query) || colName.includes(query) || label.includes(query) || tableHasMatch;

                    if (matchesType && matchesQuery) {
                        $(this).removeClass('d-none');
                        tableHasMatch = true;

                        // Live text highlight
                        if (query) {
                            highlightText($(this).find('.col-display-label'), query);
                        } else {
                            removeHighlight($(this).find('.col-display-label'));
                        }
                    } else {
                        $(this).addClass('d-none');
                        removeHighlight($(this).find('.col-display-label'));
                    }
                });

                if (tableHasMatch) {
                    $(this).removeClass('d-none');
                    if (query) $(this).find('.accordion-collapse').addClass('show');
                    moduleHasMatch = true;
                } else {
                    $(this).addClass('d-none');
                }
            });

            if (moduleHasMatch) {
                $(this).removeClass('d-none');
            } else {
                $(this).addClass('d-none');
            }
        });
    }

    function highlightText($el, term) {
        const original = $el.text();
        const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
        $el.html(original.replace(regex, '<span class="search-highlight">$1</span>'));
    }

    function removeHighlight($el) {
        $el.html($el.text());
    }

    function escapeRegex(string) {
        return string.replace(/[/\-\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    // =============================================================
    // 3. DRAG & DROP AND SORTABLE COLUMNS
    // =============================================================
    const dropZone = document.getElementById('columns-drop-zone');

    $(document).on('dragstart', '.dictionary-column-item', function(e) {
        const dragData = {
            table: $(this).data('table'),
            column: $(this).data('column'),
            label: $(this).data('label'),
            type: $(this).data('type'),
            aggregatable: $(this).data('aggregatable') == '1'
        };
        e.originalEvent.dataTransfer.setData('application/json', JSON.stringify(dragData));
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropZone.addEventListener('dragleave', function() {
        $(this).removeClass('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        try {
            const raw = e.dataTransfer.getData('application/json');
            if (raw) {
                const item = JSON.parse(raw);
                addColumnToState(item);
            }
        } catch(err) {
            console.error('Drop error:', err);
        }
    });

    // Quick Add (+) Click
    $(document).on('click', '.btn-add-column', function(e) {
        e.stopPropagation();
        const p = $(this).closest('.dictionary-column-item');
        p.addClass('pulse-added');
        setTimeout(() => p.removeClass('pulse-added'), 600);

        addColumnToState({
            table: p.data('table'),
            column: p.data('column'),
            label: p.data('label'),
            type: p.data('type'),
            aggregatable: p.data('aggregatable') == '1'
        });
    });

    function addColumnToState(col) {
        state.selected_fields.push({
            table: col.table,
            column: col.column,
            alias: col.label,
            aggregate: '',
            data_type: col.type,
            display_name: col.label,
            aggregatable: col.aggregatable
        });
        markDirty();
        renderSelectedColumns();
    }

    function renderSelectedColumns() {
        const container = $('#selected-columns-container');
        try {
            container.find('.select2-hidden-accessible').select2('destroy');
        } catch(e) {}
        container.empty();

        if (state.selected_fields.length === 0) {
            $('#columns-empty-notice').removeClass('d-none');
            $('#selected-columns-counter').text('0 Columns');
            return;
        }

        $('#columns-empty-notice').addClass('d-none');
        $('#selected-columns-counter').text(`${state.selected_fields.length} Column${state.selected_fields.length > 1 ? 's' : ''}`);

        state.selected_fields.forEach((field, index) => {
            const isAggregated = !!field.aggregate;
            const isAggregatable = field.aggregatable;
            const cardClass = isAggregated ? 'is-metric' : 'is-dimension';

            const card = $(`
                <div class="selected-column-card ${cardClass}" data-index="${index}" draggable="true">
                    <i class="fa fa-grip-lines drag-handle me-1" title="Drag to reorder"></i>

                    <!-- Column Meta -->
                    <div class="d-flex align-items-center gap-1 text-truncate" style="min-width: 170px; max-width: 200px;">
                        <div>
                            <span class="badge ${isAggregated ? 'bg-success-light text-success' : 'bg-primary-light text-primary'} font-monospace" style="font-size: 9px;">
                                ${field.table}
                            </span>
                            <div class="fw-bold text-truncate fs-xs" title="${field.column}">${field.column}</div>
                        </div>
                    </div>

                    <!-- Custom Alias Input -->
                    <div class="flex-grow-1">
                        <input type="text" class="form-control form-control-sm col-alias-input bg-white"
                               placeholder="Display Alias"
                               value="${escapeHtml(field.alias)}"
                               title="Column header in output">
                    </div>

                    <!-- Aggregate Selector -->
                    <div style="width: 140px; flex-shrink: 0;">
                        <select class="form-select form-select-sm col-aggregate-select">
                            <option value="" ${field.aggregate === '' ? 'selected' : ''}>— Group Dimension</option>
                            <option value="sum" ${field.aggregate === 'sum' ? 'selected' : ''} ${!isAggregatable ? 'disabled' : ''}>SUM()</option>
                            <option value="avg" ${field.aggregate === 'avg' ? 'selected' : ''} ${!isAggregatable ? 'disabled' : ''}>AVG()</option>
                            <option value="count" ${field.aggregate === 'count' ? 'selected' : ''}>COUNT()</option>
                            <option value="max" ${field.aggregate === 'max' ? 'selected' : ''}>MAX()</option>
                            <option value="min" ${field.aggregate === 'min' ? 'selected' : ''}>MIN()</option>
                        </select>
                    </div>

                    <!-- Up / Down Reorder Buttons -->
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-xs btn-alt-secondary btn-move-up" ${index === 0 ? 'disabled' : ''} title="Move Up">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-alt-secondary btn-move-down" ${index === state.selected_fields.length - 1 ? 'disabled' : ''} title="Move Down">
                            <i class="fa fa-arrow-down"></i>
                        </button>
                    </div>

                    <!-- Remove Button -->
                    <button type="button" class="btn-remove-condition btn-remove-col ms-1" title="Remove column">
                        <i class="fa fa-trash-can fs-xs"></i>
                    </button>
                </div>
            `);

            container.append(card);
        });

        // Initialize drag-to-reorder on selected column cards
        initCardDragReorder();

        if (typeof $.fn.select2 !== 'undefined') {
            container.find('.col-aggregate-select').select2({
                width: '100%'
            });
        }
    }

    // Card Drag Reordering
    let draggedIndex = null;
    function initCardDragReorder() {
        const cards = document.querySelectorAll('.selected-column-card');
        cards.forEach(card => {
            card.addEventListener('dragstart', function(e) {
                draggedIndex = parseInt(this.getAttribute('data-index'));
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            card.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });

            card.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });

            card.addEventListener('drop', function(e) {
                e.preventDefault();
                const targetIndex = parseInt(this.getAttribute('data-index'));
                if (draggedIndex !== null && draggedIndex !== targetIndex) {
                    const movedItem = state.selected_fields.splice(draggedIndex, 1)[0];
                    state.selected_fields.splice(targetIndex, 0, movedItem);
                    markDirty();
                    renderSelectedColumns();
                }
            });
        });
    }

    // Move Up / Move Down
    $(document).on('click', '.btn-move-up', function() {
        const idx = $(this).closest('.selected-column-card').data('index');
        if (idx > 0) {
            const item = state.selected_fields.splice(idx, 1)[0];
            state.selected_fields.splice(idx - 1, 0, item);
            markDirty();
            renderSelectedColumns();
        }
    });

    $(document).on('click', '.btn-move-down', function() {
        const idx = $(this).closest('.selected-column-card').data('index');
        if (idx < state.selected_fields.length - 1) {
            const item = state.selected_fields.splice(idx, 1)[0];
            state.selected_fields.splice(idx + 1, 0, item);
            markDirty();
            renderSelectedColumns();
        }
    });

    // Change Alias & Aggregate
    $(document).on('input', '.col-alias-input', function() {
        const idx = $(this).closest('.selected-column-card').data('index');
        state.selected_fields[idx].alias = $(this).val();
        markDirty();
    });

    $(document).on('change', '.col-aggregate-select', function() {
        const idx = $(this).closest('.selected-column-card').data('index');
        state.selected_fields[idx].aggregate = $(this).val();
        markDirty();
        renderSelectedColumns(); // Re-render to update dimension/metric styling
    });

    $(document).on('click', '.btn-remove-col', function() {
        const idx = $(this).closest('.selected-column-card').data('index');
        state.selected_fields.splice(idx, 1);
        markDirty();
        renderSelectedColumns();
    });

    $('#btn-clear-columns').on('click', function() {
        if (state.selected_fields.length === 0) return;
        Swal.fire({
            title: 'Clear All Columns?',
            text: 'Are you sure you want to remove all selected columns?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear all'
        }).then(res => {
            if (res.isConfirmed) {
                state.selected_fields = [];
                markDirty();
                renderSelectedColumns();
            }
        });
    });

    // =============================================================
    // 4. SMART FILTER CRITERIA TREE
    // =============================================================
    function renderFilterTree() {
        const rootContainer = $('#filter-tree-container');
        try {
            rootContainer.find('.select2-hidden-accessible').select2('destroy');
        } catch(e) {}
        rootContainer.empty();
        rootContainer.append(buildGroupHtml(state.filters, true, []));
        initFlatpickrInputs();
        initFilterSelect2();
    }

    function buildGroupHtml(group, isRoot, path) {
        const isOr = (group.logical_operator || 'AND').toUpperCase() === 'OR';
        const pathStr = path.join('-');

        const groupCard = $(`
            <div class="filter-group-card ${isOr ? 'group-or' : 'group-and'}" data-path="${pathStr}">
                <div class="filter-group-header">
                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                        <span class="fs-xs fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 11px; white-space: nowrap;">Condition Logic</span>
                        <div class="btn-group btn-group-sm" role="group" style="flex-shrink: 0;">
                            <button type="button" class="btn btn-xs ${!isOr ? 'btn-primary active' : 'btn-alt-secondary'} btn-toggle-logic" data-val="AND">ALL (AND)</button>
                            <button type="button" class="btn btn-xs ${isOr ? 'btn-warning active text-dark' : 'btn-alt-secondary'} btn-toggle-logic" data-val="OR">ANY (OR)</button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-nowrap" style="flex-shrink: 0;">
                        <button type="button" class="btn btn-xs btn-alt-primary btn-add-condition-to-group" title="Add condition">
                            <i class="fa fa-plus me-1"></i> Condition
                        </button>
                        <button type="button" class="btn btn-xs btn-alt-secondary btn-add-subgroup" title="Add nested group">
                            <i class="fa fa-diagram-project me-1"></i> Group
                        </button>
                        ${!isRoot ? `
                            <button type="button" class="btn btn-xs btn-alt-danger ms-1 btn-remove-group" title="Remove group">
                                <i class="fa fa-trash-can me-1"></i> Remove
                            </button>
                        ` : ''}
                    </div>
                </div>
                <div class="filter-children-container"></div>
            </div>
        `);

        const childrenContainer = groupCard.find('.filter-children-container');

        if (!group.conditions || group.conditions.length === 0) {
            childrenContainer.append(`
                <div class="text-muted fs-xs fst-italic py-2 text-center empty-group-placeholder">
                    No filter conditions added. Click <strong>+ Condition</strong> above.
                </div>
            `);
        } else {
            group.conditions.forEach((child, idx) => {
                const childPath = [...path, idx];
                if (child.logical_operator && child.conditions) {
                    childrenContainer.append(buildGroupHtml(child, false, childPath));
                } else {
                    childrenContainer.append(buildConditionRowHtml(child, childPath));
                }
            });
        }

        return groupCard;
    }

    function buildConditionRowHtml(condition, path) {
        const pathStr = path.join('-');

        let optionsHtml = '<option value="">-- Choose Field --</option>';
        for (const t in dictionaryLookup) {
            optionsHtml += `<optgroup label="${t}">`;
            for (const c in dictionaryLookup[t]) {
                const item = dictionaryLookup[t][c];
                const selected = (condition.table === t && condition.column === c) ? 'selected' : '';
                optionsHtml += `<option value="${t}.${c}" ${selected}>${t}.${item.label} (${c})</option>`;
            }
            optionsHtml += `</optgroup>`;
        }

        const op = condition.operator || '=';
        const isNullOp = ['is_null', 'is_not_null'].includes(op);
        const colLookup = (condition.table && condition.column && dictionaryLookup[condition.table])
            ? dictionaryLookup[condition.table][condition.column]
            : null;
        const isDateField = colLookup && (colLookup.type === 'date' || colLookup.type === 'datetime');

        return $(`
            <div class="filter-condition-row" data-path="${pathStr}">
                <!-- Field Dropdown -->
                <div style="min-width: 200px; flex: 2;">
                    <select class="form-select form-select-sm cond-field-select bg-light">
                        ${optionsHtml}
                    </select>
                </div>

                <!-- Operator Dropdown -->
                <div style="min-width: 140px; flex: 1;">
                    <select class="form-select form-select-sm cond-operator-select">
                        <option value="=" ${op === '=' ? 'selected' : ''}>Equals (=)</option>
                        <option value="!=" ${op === '!=' ? 'selected' : ''}>Not Equals (!=)</option>
                        <option value=">" ${op === '>' ? 'selected' : ''}>Greater Than (&gt;)</option>
                        <option value=">=" ${op === '>=' ? 'selected' : ''}>Greater or Equal (&gt;=)</option>
                        <option value="<" ${op === '<' ? 'selected' : ''}>Less Than (&lt;)</option>
                        <option value="<=" ${op === '<=' ? 'selected' : ''}>Less or Equal (&lt;=)</option>
                        <option value="contains" ${op === 'contains' ? 'selected' : ''}>Contains (ILIKE)</option>
                        <option value="starts_with" ${op === 'starts_with' ? 'selected' : ''}>Starts With</option>
                        <option value="ends_with" ${op === 'ends_with' ? 'selected' : ''}>Ends With</option>
                        <option value="in" ${op === 'in' ? 'selected' : ''}>In (A, B, C)</option>
                        <option value="not_in" ${op === 'not_in' ? 'selected' : ''}>Not In</option>
                        <option value="is_null" ${op === 'is_null' ? 'selected' : ''}>Is Null</option>
                        <option value="is_not_null" ${op === 'is_not_null' ? 'selected' : ''}>Is Not Null</option>
                        <option value="between" ${op === 'between' ? 'selected' : ''}>Between (Min, Max)</option>
                    </select>
                </div>

                <!-- Value Input (Flatpickr aware for dates) -->
                <div style="min-width: 180px; flex: 2;" class="cond-value-wrapper ${isNullOp ? 'd-none' : ''}">
                    <input type="text"
                           class="form-control form-control-sm cond-value-input ${isDateField ? 'js-flatpickr-input' : ''}"
                           placeholder="${isDateField ? 'Pick Date or Range...' : 'Filter value...'}"
                           value="${escapeHtml(condition.value || '')}">
                    ${isDateField ? `
                        <div class="d-flex gap-1 mt-1">
                            <span class="date-preset-badge" data-val="today">Today</span>
                            <span class="date-preset-badge" data-val="this_month">This Month</span>
                            <span class="date-preset-badge" data-val="this_year">This Year</span>
                        </div>
                    ` : ''}
                </div>

                <!-- Remove Button -->
                <button type="button" class="btn-remove-condition ms-auto" title="Delete condition">
                    <i class="fa fa-trash-can fs-xs"></i>
                </button>
            </div>
        `);
    }

    function initFlatpickrInputs() {
        if (typeof flatpickr !== 'undefined') {
            document.querySelectorAll('.js-flatpickr-input').forEach(el => {
                flatpickr(el, {
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
            });
        }
    }

    function initFilterSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        $('#filter-tree-container .cond-field-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    placeholder: '-- Choose Field --',
                    width: '100%',
                    dropdownAutoWidth: true
                });
            }
        });

        $('#filter-tree-container .cond-operator-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    width: '100%',
                    dropdownAutoWidth: true
                });
            }
        });
    }

    // Quick Date Presets Click
    $(document).on('click', '.date-preset-badge', function() {
        const val = $(this).data('val');
        const input = $(this).closest('.cond-value-wrapper').find('.cond-value-input');
        const now = new Date();

        let dateStr = '';
        if (val === 'today') {
            dateStr = now.toISOString().slice(0, 10);
        } else if (val === 'this_month') {
            dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
        } else if (val === 'this_year') {
            dateStr = `${now.getFullYear()}`;
        }

        input.val(dateStr).trigger('input');
    });

    function getElementPath($el) {
        if (!$el || $el.length === 0) return [];
        const raw = $el.attr('data-path');
        if (raw === undefined || raw === null || raw === '') {
            return [];
        }
        return String(raw).split('-').map(Number);
    }

    function getGroupRefByPath(path) {
        let curr = state.filters;
        for (let i = 0; i < path.length; i++) {
            curr = curr.conditions[path[i]];
        }
        return curr;
    }

    // Toggle Logic
    $(document).on('click', '.btn-toggle-logic', function() {
        const val = $(this).data('val');
        const path = getElementPath($(this).closest('.filter-group-card'));

        let group = path.length === 0 ? state.filters : getGroupRefByPath(path);
        group.logical_operator = val;
        markDirty();
        renderFilterTree();
    });

    // Add Condition
    $(document).on('click', '.btn-add-condition-to-group, #btn-add-root-condition', function() {
        const isRoot = $(this).is('#btn-add-root-condition');
        const path = isRoot ? [] : getElementPath($(this).closest('.filter-group-card'));

        let group = path.length === 0 ? state.filters : getGroupRefByPath(path);
        if (!group.conditions) group.conditions = [];

        group.conditions.push({ table: '', column: '', operator: '=', value: '' });
        markDirty();
        renderFilterTree();
    });

    // Add Sub-Group
    $(document).on('click', '.btn-add-subgroup, #btn-add-root-group', function() {
        const isRoot = $(this).is('#btn-add-root-group');
        const path = isRoot ? [] : getElementPath($(this).closest('.filter-group-card'));

        let group = path.length === 0 ? state.filters : getGroupRefByPath(path);
        if (!group.conditions) group.conditions = [];

        group.conditions.push({ logical_operator: 'AND', conditions: [] });
        markDirty();
        renderFilterTree();
    });

    // Change Field
    $(document).on('change', '.cond-field-select', function() {
        const path = getElementPath($(this).closest('.filter-condition-row'));
        const val = $(this).val();
        const target = getGroupRefByPath(path);
        if (!target) return;

        if (val) {
            const parts = val.split('.');
            target.table = parts[0];
            target.column = parts[1];
        } else {
            target.table = '';
            target.column = '';
        }
        markDirty();
        renderFilterTree(); // Re-render to adapt flatpickr if date
    });

    // Change Operator
    $(document).on('change', '.cond-operator-select', function() {
        const path = getElementPath($(this).closest('.filter-condition-row'));
        const op = $(this).val();
        const target = getGroupRefByPath(path);
        if (!target) return;
        target.operator = op;

        const valWrapper = $(this).closest('.filter-condition-row').find('.cond-value-wrapper');
        if (['is_null', 'is_not_null'].includes(op)) {
            valWrapper.addClass('d-none');
            target.value = '';
        } else {
            valWrapper.removeClass('d-none');
        }
        markDirty();
    });

    // Change Value
    $(document).on('input', '.cond-value-input', function() {
        const path = getElementPath($(this).closest('.filter-condition-row'));
        const target = getGroupRefByPath(path);
        if (!target) return;
        target.value = $(this).val();
        markDirty();
    });

    // Remove Condition / Group
    $(document).on('click', '.btn-remove-condition, .btn-remove-group', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const isGroup = $(this).hasClass('btn-remove-group');
        const el = isGroup ? $(this).closest('.filter-group-card') : $(this).closest('.filter-condition-row');
        const path = getElementPath(el);
        if (path.length === 0) return;

        const removeIdx = path.pop();
        const parentGroup = path.length === 0 ? state.filters : getGroupRefByPath(path);
        if (parentGroup && parentGroup.conditions) {
            parentGroup.conditions.splice(removeIdx, 1);
            markDirty();
            renderFilterTree();
        }
    });

    // =============================================================
    // 5. MULTI-COLUMN SORTING PANEL
    // =============================================================
    function renderSorts() {
        const container = $('#sorts-container');
        try {
            container.find('.select2-hidden-accessible').select2('destroy');
        } catch(e) {}
        container.empty();

        if (state.sorts.length === 0) {
            container.append(`
                <div class="text-muted fs-xs fst-italic text-center py-2" id="sorts-empty-notice">
                    No sorting rules applied. Default database order will be used.
                </div>
            `);
            return;
        }

        state.sorts.forEach((sort, idx) => {
            let rowOptionsHtml = '<option value="">-- Choose Column --</option>';
            for (const t in dictionaryLookup) {
                rowOptionsHtml += `<optgroup label="${t}">`;
                for (const c in dictionaryLookup[t]) {
                    const item = dictionaryLookup[t][c];
                    const isSelected = (sort.table === t && sort.column === c) ? 'selected' : '';
                    rowOptionsHtml += `<option value="${t}.${c}" ${isSelected}>${t}.${item.label} (${c})</option>`;
                }
                rowOptionsHtml += `</optgroup>`;
            }

            const row = $(`
                <div class="sort-row d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded" data-index="${idx}">
                    <span class="fs-xs fw-bold text-muted flex-shrink-0" style="width: 55px;">Sort #${idx + 1}</span>
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <select class="form-select form-select-sm sort-field-select">
                            ${rowOptionsHtml}
                        </select>
                    </div>
                    <div style="width: 170px; flex-shrink: 0;">
                        <select class="form-select form-select-sm sort-dir-select">
                            <option value="asc" ${sort.direction === 'asc' ? 'selected' : ''}>Ascending (A-Z, 0-9)</option>
                            <option value="desc" ${sort.direction === 'desc' ? 'selected' : ''}>Descending (Z-A, 9-0)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-remove-condition btn-remove-sort ms-auto" title="Remove sort rule">
                        <i class="fa fa-trash-can fs-xs"></i>
                    </button>
                </div>
            `);

            container.append(row);
        });

        initSortSelect2();
    }

    function initSortSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        $('#sorts-container .sort-field-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    placeholder: '-- Choose Column --',
                    width: '100%',
                    dropdownAutoWidth: true
                });
            }
        });

        $('#sorts-container .sort-dir-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    width: '100%'
                });
            }
        });
    }

    $('#btn-add-sort').on('click', function() {
        state.sorts.push({ table: '', column: '', direction: 'asc' });
        markDirty();
        renderSorts();
    });

    $(document).on('change', '.sort-field-select', function() {
        const idx = $(this).closest('[data-index]').data('index');
        const val = $(this).val();
        if (val) {
            const parts = val.split('.');
            state.sorts[idx].table = parts[0];
            state.sorts[idx].column = parts[1];
        } else {
            state.sorts[idx].table = '';
            state.sorts[idx].column = '';
        }
        markDirty();
    });

    $(document).on('change', '.sort-dir-select', function() {
        const idx = $(this).closest('[data-index]').data('index');
        state.sorts[idx].direction = $(this).val();
        markDirty();
    });

    $(document).on('click', '.btn-remove-sort', function() {
        const idx = $(this).closest('[data-index]').data('index');
        state.sorts.splice(idx, 1);
        markDirty();
        renderSorts();
    });

    // =============================================================
    // 6. PREVIEW REPORT & KPI SUMMARY CARDS
    // =============================================================
    $('#btn-preview-report').on('click', function() {
        if (state.selected_fields.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Columns Selected',
                text: 'Please select at least one column from the catalog.'
            });
            return;
        }

        const payload = cleanStateForPayload();
        const btn = $(this);
        const startTime = performance.now();

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Running Query...');
        $('#results-meta-badges').html('<span class="badge bg-warning"><i class="fa fa-spinner fa-spin me-1"></i> Compiling...</span>');

        $.ajax({
            url: "{{ route('reports.preview') }}",
            type: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function(res) {
                const duration = Math.round(performance.now() - startTime);
                btn.prop('disabled', false).html('<i class="fa fa-play me-1"></i> <strong>Preview Report</strong>');

                if (res.success) {
                    rawPreviewRows = res.rows || [];
                    renderPreviewTable(res.headers, rawPreviewRows);
                    $('#sql-preview-text').text(res.sql);

                    // Update meta badges & duration
                    $('#query-timer-badge').html(`<i class="fa fa-bolt text-warning me-1"></i> Executed in ${duration}ms`);
                    $('#results-meta-badges').html(`
                        <span class="badge bg-success me-1"><i class="fa fa-check me-1"></i> ${res.preview_count} rows previewed</span>
                        <span class="badge bg-info">Total Records: ${res.total_count.toLocaleString()}</span>
                    `);
                    $('#tab-results-badge').removeClass('d-none').text(res.preview_count);

                    // Calculate KPI Metric Cards
                    computeKpiSummary(res.headers, rawPreviewRows, res.total_count);

                    // Switch to Data Preview Tab
                    $('#tab-results-btn').tab('show');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-play me-1"></i> <strong>Preview Report</strong>');
                const msg = xhr.responseJSON?.message || 'Error compiling custom report query.';
                $('#results-meta-badges').html('<span class="badge bg-danger"><i class="fa fa-triangle-exclamation me-1"></i> Error</span>');
                Swal.fire('Query Error', msg, 'error');
            }
        });
    });

    function computeKpiSummary(headers, rows, totalCount) {
        $('#kpi-total-rows').text(totalCount.toLocaleString());
        $('#kpi-preview-rows').text(`Showing top ${rows.length}`);
        $('#kpi-summary-cards').show();

        // Find first numeric column for summary stats
        const numericCol = headers.find(h => ['decimal', 'integer'].includes(h.data_type));
        if (numericCol && rows.length > 0) {
            let sum = 0;
            let validCount = 0;
            rows.forEach(r => {
                const val = parseFloat(r[numericCol.key]);
                if (!isNaN(val)) {
                    sum += val;
                    validCount++;
                }
            });

            const avg = validCount > 0 ? (sum / validCount) : 0;
            $('#kpi-sum-label').text(`Sum (${numericCol.alias})`);
            $('#kpi-sum-val').text(sum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#kpi-avg-label').text(`Average (${numericCol.alias})`);
            $('#kpi-avg-val').text(avg.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            $('#kpi-sum-card').show();
            $('#kpi-avg-card').show();
        } else {
            $('#kpi-sum-card').hide();
            $('#kpi-avg-card').hide();
        }
    }

    function renderPreviewTable(headers, rows) {
        const thead = $('#preview-table-head');
        const tbody = $('#preview-table-body');
        thead.empty();
        tbody.empty();

        if (!rows || rows.length === 0) {
            thead.append('<tr><th class="py-2 text-center text-muted">No Results Found</th></tr>');
            tbody.append('<tr><td class="text-center py-5 text-muted">The query completed successfully, but returned 0 records matching your criteria.</td></tr>');
            return;
        }

        // Header Row with column data-type icons
        let headRow = '<tr>';
        headers.forEach(h => {
            const isNumeric = ['decimal', 'integer'].includes(h.data_type);
            const isDate = ['date', 'datetime'].includes(h.data_type);
            const icon = isNumeric ? '<span class="text-info me-1">#</span>' : (isDate ? '<span class="text-warning me-1">📅</span>' : '<span class="text-muted me-1">🔤</span>');
            const aggBadge = h.aggregate ? `<span class="badge bg-success-light text-success ms-1" style="font-size: 9px;">${h.aggregate.toUpperCase()}</span>` : '';

            headRow += `<th class="${isNumeric ? 'text-end' : ''} py-2" title="${h.table}.${h.column}">
                ${icon}${escapeHtml(h.alias)} ${aggBadge}
            </th>`;
        });
        headRow += '</tr>';
        thead.append(headRow);

        // Body Rows
        rows.forEach(r => {
            let rowHtml = '<tr>';
            headers.forEach(h => {
                const key = h.key;
                const val = r[key] !== undefined && r[key] !== null ? r[key] : '';
                const isNumeric = ['decimal', 'integer'].includes(h.data_type);
                rowHtml += `<td class="${isNumeric ? 'text-end font-monospace' : ''} py-1">${escapeHtml(String(val))}</td>`;
            });
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        });
    }

    // In-Table Search Filter
    $('#preview-filter-input').on('input', function() {
        const term = $(this).val().toLowerCase().trim();
        const rows = $('#preview-table-body tr');

        if (!term) {
            rows.show();
            $('#preview-filter-count').text('');
            return;
        }

        let visibleCount = 0;
        rows.each(function() {
            const text = $(this).text().toLowerCase();
            const match = text.includes(term);
            $(this).toggle(match);
            if (match) visibleCount++;
        });

        $('#preview-filter-count').text(`Matching ${visibleCount} of ${rows.length} preview rows`);
    });

    // Fullscreen Mode Toggle
    $('#btn-fullscreen-table').on('click', function() {
        $('#report-studio-wrapper').toggleClass('studio-fullscreen');
        const isFull = $('#report-studio-wrapper').hasClass('studio-fullscreen');
        $(this).html(isFull ? '<i class="fa fa-compress"></i>' : '<i class="fa fa-expand"></i>');
    });

    // Copy SQL
    $('#btn-copy-sql').on('click', function() {
        const sql = $('#sql-preview-text').text();
        navigator.clipboard.writeText(sql).then(() => {
            One.helpers('jq-notify', { type: 'success', icon: 'fa fa-check me-1', message: 'SQL copied to clipboard!' });
        });
    });

    // =============================================================
    // 7. EXPORT DATA (CSV & EXCEL)
    // =============================================================
    $('#btn-export-csv').on('click', function() { triggerExport('csv'); });
    $('#btn-export-excel').on('click', function() { triggerExport('excel'); });

    function triggerExport(format) {
        if (state.selected_fields.length === 0) {
            Swal.fire('No Fields Selected', 'Please select fields before exporting.', 'warning');
            return;
        }

        const payload = cleanStateForPayload();
        payload.export_format = format;

        const form = $('#export-form');
        form.empty();
        form.append(`@csrf`);
        form.append(`<input type="hidden" name="export_format" value="${format}">`);

        payload.selected_fields.forEach((f, idx) => {
            form.append(`<input type="hidden" name="selected_fields[${idx}][table]" value="${f.table}">`);
            form.append(`<input type="hidden" name="selected_fields[${idx}][column]" value="${f.column}">`);
            form.append(`<input type="hidden" name="selected_fields[${idx}][alias]" value="${escapeHtml(f.alias || '')}">`);
            form.append(`<input type="hidden" name="selected_fields[${idx}][aggregate]" value="${f.aggregate || ''}">`);
        });

        if (payload.filters && payload.filters.conditions && payload.filters.conditions.length > 0) {
            appendFilterInputs(form, payload.filters, 'filters');
        }

        if (payload.sorts && payload.sorts.length > 0) {
            payload.sorts.forEach((s, idx) => {
                form.append(`<input type="hidden" name="sorts[${idx}][table]" value="${s.table}">`);
                form.append(`<input type="hidden" name="sorts[${idx}][column]" value="${s.column}">`);
                form.append(`<input type="hidden" name="sorts[${idx}][direction]" value="${s.direction}">`);
            });
        }

        form.submit();
    }

    function appendFilterInputs(form, group, prefix) {
        form.append(`<input type="hidden" name="${prefix}[logical_operator]" value="${group.logical_operator || 'AND'}">`);
        (group.conditions || []).forEach((c, idx) => {
            const childPrefix = `${prefix}[conditions][${idx}]`;
            if (c.logical_operator && c.conditions) {
                appendFilterInputs(form, c, childPrefix);
            } else {
                form.append(`<input type="hidden" name="${childPrefix}[table]" value="${c.table}">`);
                form.append(`<input type="hidden" name="${childPrefix}[column]" value="${c.column}">`);
                form.append(`<input type="hidden" name="${childPrefix}[operator]" value="${c.operator || '='}">`);
                form.append(`<input type="hidden" name="${childPrefix}[value]" value="${escapeHtml(c.value || '')}">`);
            }
        });
    }

    // =============================================================
    // 8. TEMPLATES MANAGEMENT (SAVE / LOAD)
    // =============================================================
    $('#btn-open-save-modal').on('click', function() {
        if (state.selected_fields.length === 0) {
            Swal.fire('Nothing to Save', 'Please select at least one column before saving.', 'warning');
            return;
        }

        if (state.current_template) {
            $('#template-id-input').val(state.current_template.id);
            $('#template-name-input').val(state.current_template.name);
            $('#template-desc-input').val(state.current_template.description || '');
            if (state.current_template.is_shared) {
                $('#vis-shared').prop('checked', true);
            } else {
                $('#vis-private').prop('checked', true);
            }
            $('#overwrite-template-name').text(state.current_template.name);
            $('#save-mode-group').removeClass('d-none');
        } else {
            $('#template-id-input').val('');
            $('#template-name-input').val(state.title || '');
            $('#template-desc-input').val('');
            $('#vis-private').prop('checked', true);
            $('#save-mode-group').addClass('d-none');
        }

        $('#modal-save-template').modal('show');
    });

    $('#btn-confirm-save-template').on('click', function() {
        const name = $('#template-name-input').val().trim();
        if (!name) {
            Swal.fire('Required', 'Please enter a template name.', 'warning');
            return;
        }

        const isShared = $('input[name="template-visibility"]:checked').val() == '1';
        const saveMode = $('input[name="save-mode"]:checked').val();
        const templateId = (saveMode === 'new' || !$('#template-id-input').val()) ? null : $('#template-id-input').val();

        const payload = {
            name: name,
            description: $('#template-desc-input').val().trim(),
            is_shared: isShared,
            template_id: templateId,
            query_config: cleanStateForPayload()
        };

        $.ajax({
            url: "{{ route('reports.save-template') }}",
            type: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function(res) {
                $('#modal-save-template').modal('hide');
                state.current_template = res.template;
                state.title = res.template.name;
                $('#report-title-field').val(state.title);
                markClean();
                updateActiveTemplateBanner();
                One.helpers('jq-notify', { type: 'success', icon: 'fa fa-check me-1', message: res.message });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save template', 'error');
            }
        });
    });

    // Load Template Click
    $(document).on('click', '.btn-load-tpl', function() {
        const tpl = $(this).data('template');
        loadTemplateIntoState(tpl);
        $('#modal-load-template').modal('hide');
    });

    function loadTemplateIntoState(tpl) {
        state.current_template = tpl;
        state.title = tpl.name;
        $('#report-title-field').val(tpl.name);

        const config = tpl.query_config || {};

        state.selected_fields = (config.selected_fields || []).map(f => {
            const lookup = (dictionaryLookup[f.table] && dictionaryLookup[f.table][f.column]) ? dictionaryLookup[f.table][f.column] : null;
            return {
                table: f.table,
                column: f.column,
                alias: f.alias || (lookup ? lookup.label : f.column),
                aggregate: f.aggregate || '',
                data_type: lookup ? lookup.type : 'string',
                display_name: lookup ? lookup.label : f.column,
                aggregatable: lookup ? lookup.aggregatable : false
            };
        });

        state.filters = config.filters || { logical_operator: 'AND', conditions: [] };
        state.sorts = config.sorts || [];

        markClean();
        renderSelectedColumns();
        renderFilterTree();
        renderSorts();
        updateActiveTemplateBanner();

        One.helpers('jq-notify', {
            type: 'info',
            icon: 'fa fa-folder-open me-1',
            message: `Loaded template: ${tpl.name}`
        });
    }

    // Delete Template
    $(document).on('click', '.btn-delete-tpl', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete Template?',
            text: 'Are you sure you want to permanently delete this report template?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('reports/delete-template') }}/${id}`,
                    type: "DELETE",
                    success: function() {
                        row.fadeOut(300, function() { $(this).remove(); });
                        if (state.current_template && state.current_template.id == id) {
                            state.current_template = null;
                            updateActiveTemplateBanner();
                        }
                        One.helpers('jq-notify', { type: 'success', message: 'Template removed.' });
                    }
                });
            }
        });
    });

    function updateActiveTemplateBanner() {
        const pill = $('#active-template-pill');
        if (state.current_template) {
            const isShared = state.current_template.is_shared;
            pill.removeClass('d-none bg-secondary-light text-secondary bg-success-light text-success')
                .addClass(isShared ? 'bg-success-light text-success' : 'bg-secondary-light text-secondary')
                .html(`<i class="fa ${isShared ? 'fa-globe' : 'fa-lock'} me-1"></i> ${state.current_template.name}`);
        } else {
            pill.addClass('d-none');
        }
    }

    // Reset Builder
    $('#btn-reset-builder').on('click', function() {
        Swal.fire({
            title: 'Reset Builder?',
            text: 'This will reset all selected columns, filters, and sorts.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reset'
        }).then(res => {
            if (res.isConfirmed) {
                state.title = 'Untitled Custom Report';
                state.selected_fields = [];
                state.filters = { logical_operator: 'AND', conditions: [] };
                state.sorts = [];
                state.current_template = null;
                $('#report-title-field').val(state.title);
                markClean();
                renderSelectedColumns();
                renderFilterTree();
                renderSorts();
                updateActiveTemplateBanner();

                $('#preview-table-head').html('<tr><th class="py-2 text-center text-muted">No columns selected</th></tr>');
                $('#preview-table-body').html('<tr><td class="text-center py-5 text-muted">Select columns and click Preview Report to load live data.</td></tr>');
                $('#sql-preview-text').text('-- SQL query will appear here upon preview');
                $('#results-meta-badges').html('<span class="badge bg-secondary">Awaiting execution</span>');
                $('#kpi-summary-cards').hide();
                $('#tab-results-badge').addClass('d-none');
            }
        });
    });

    // Quick Sync Schema
    $('#btn-quick-sync').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).find('i').addClass('fa-spin');

        $.post("{{ route('reports.dictionary.sync') }}", function(res) {
            btn.prop('disabled', false).find('i').removeClass('fa-spin');
            Swal.fire('Synchronized', 'Database schema synchronized with data catalog.', 'success')
                .then(() => location.reload());
        }).fail(function() {
            btn.prop('disabled', false).find('i').removeClass('fa-spin');
            Swal.fire('Error', 'Schema sync failed.', 'error');
        });
    });

    // =============================================================
    // HELPER: CLEAN STATE FOR BACKEND PAYLOAD
    // =============================================================
    function cleanStateForPayload() {
        return {
            selected_fields: state.selected_fields.map(f => ({
                table: f.table,
                column: f.column,
                alias: f.alias,
                aggregate: f.aggregate || ''
            })),
            filters: cleanFilterGroup(state.filters),
            sorts: state.sorts.filter(s => s.table && s.column)
        };
    }

    function cleanFilterGroup(group) {
        if (!group || !group.conditions) return null;
        const validConditions = [];

        group.conditions.forEach(c => {
            if (c.logical_operator && c.conditions) {
                const cleanedSub = cleanFilterGroup(c);
                if (cleanedSub && cleanedSub.conditions.length > 0) {
                    validConditions.push(cleanedSub);
                }
            } else {
                if (c.table && c.column) {
                    validConditions.push({
                        table: c.table,
                        column: c.column,
                        operator: c.operator || '=',
                        value: c.value !== undefined ? c.value : ''
                    });
                }
            }
        });

        if (validConditions.length === 0) return null;

        return {
            logical_operator: group.logical_operator || 'AND',
            conditions: validConditions
        };
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

    // Initialize root views
    renderFilterTree();
    renderSorts();

    @if(isset($initialTemplate) && $initialTemplate)
        loadTemplateIntoState(@json($initialTemplate));
    @endif
});
</script>
@endsection
