@extends('Partials.app', ['activeMenu' => 'data-dictionary'])

@section('title')
    Data Dictionary & Schema Catalog
@endsection

@section('page_title')
    Data Dictionary & Schema Catalog
@endsection

@section('styles')
    <!-- Page JS Plugins CSS for datatable -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 31px;
            padding: 2px 6px;
            font-size: 0.8125rem;
            border-color: #dbe1e8;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 25px;
            color: #334155;
            padding-left: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        .select2-container--default .select2-dropdown {
            border-color: #cbd5e1;
            font-size: 0.8125rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            z-index: 1060;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-color: #cbd5e1;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
        }
        .select2-container--default .select2-results__option {
            padding: 5px 8px;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
<div class="content">
    <!-- Header Block -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start mb-3">
        <div>
            <div class="text-uppercase fw-semibold fs-xs text-muted">Analytics & Custom Reports</div>
            <h2 class="h3 fw-bold mb-1">
                <i class="fa fa-book-bookmark text-primary me-2"></i> Data Dictionary & Schema Catalog
            </h2>
            <p class="text-muted mb-0">Manage reportable fields, human-readable labels, aggregations, and join relationships.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-primary" id="btn-sync-schema">
                <i class="fa fa-arrows-rotate me-1"></i> Sync Database Schema Now
            </button>
            <a href="{{ route('reports.hub') }}" class="btn btn-alt-secondary">
                <i class="fa fa-table-list me-1"></i> Reports Hub
            </a>
            <a href="{{ route('reports.builder') }}" class="btn btn-alt-secondary">
                <i class="fa fa-sliders me-1"></i> Open Report Builder
            </a>
        </div>
    </div>

    {{-- Response messages --}}
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible m-3 mb-0" role="alert">
            <small class="mb-0">{{ Session::get('success') }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible m-3 mb-0" role="alert">
            <small class="mb-0">{{ Session::get('error') }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-tabs-block mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-fields-btn" data-bs-toggle="tab" data-bs-target="#tab-fields" type="button" role="tab">
                <i class="fa fa-table-columns me-1"></i> Registered Columns (<span id="total-columns-count">{{ $totalColumns ?? 0 }}</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-joins-btn" data-bs-toggle="tab" data-bs-target="#tab-joins" type="button" role="tab">
                <i class="fa fa-link me-1"></i> Join Graph Relationships ({{ $joins->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: COLUMNS CATALOG (Yajra DataTables Server-Side) -->
        <div class="tab-pane fade show active" id="tab-fields" role="tabpanel">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <div>
                        <h3 class="block-title">Columns Whitelist</h3>
                        <div class="text-muted fs-sm">Search, filter, and configure schema properties for dynamic reports.</div>
                    </div>
                </div>

                <div class="block-content fs-sm data-content">
                    <!-- Filter Toolbar -->
                    <div class="row g-2 mb-3 align-items-center bg-light p-3 rounded border">
                        <div class="col-md-4">
                            <label class="form-label fs-xs fw-semibold text-muted mb-1" for="filter-module">Filter by Module</label>
                            <select id="filter-module" class="form-select form-select-sm">
                                <option value="all">-- All Modules --</option>
                                @foreach($modules as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-xs fw-semibold text-muted mb-1" for="filter-table">Filter by Table</label>
                            <select id="filter-table" class="form-select form-select-sm">
                                <option value="all">-- All Tables --</option>
                                @foreach($tables as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mt-auto">
                            <button type="button" id="btn-reset-filters" class="btn btn-sm btn-alt-secondary w-100">
                                <i class="fa fa-rotate-left me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>

                    <!-- Server-Side DataTable -->
                    <div class="table-responsive">
                        <table id="dictionary-table" class="table table-sm table-vcenter table-hover js-dataTable-responsive w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">SI</th>
                                    <th>Module</th>
                                    <th>Table & Column</th>
                                    <th>Display Name</th>
                                    <th class="text-center">Data Type</th>
                                    <th class="text-center">Reportable</th>
                                    <th class="text-center">Filterable</th>
                                    <th class="text-center">Aggregatable</th>
                                    <th class="text-end" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: JOIN RELATIONSHIPS -->
        <div class="tab-pane fade" id="tab-joins" role="tabpanel">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <div>
                        <h3 class="block-title">Multi-Hub Join Adjacency Graph</h3>
                        <div class="text-muted fs-sm">Registered BFS traversal paths across database tables.</div>
                    </div>
                    <div class="block-options">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-add-join">
                            <i class="fa fa-plus me-1"></i> Add Join Relationship
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm data-content">
                    <div class="table-responsive">
                        <table id="joins-table" class="table table-sm table-vcenter table-hover js-dataTable-joins w-100">
                            <thead>
                                <tr>
                                    <th>Source Table & Column</th>
                                    <th class="text-center">Join Type</th>
                                    <th>Target Table & Column</th>
                                    <th>Description</th>
                                    <th class="text-center">Priority</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($joins as $j)
                                    <tr data-id="{{ $j->id }}">
                                        <td>
                                            <strong>{{ $j->from_table }}</strong>.<code>{{ $j->from_column }}</code>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-white">{{ $j->join_type }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $j->to_table }}</strong>.<code>{{ $j->to_column }}</code>
                                        </td>
                                        <td class="text-muted fs-xs">{{ $j->description ?? '—' }}</td>
                                        <td class="text-center">{{ $j->priority }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-xs btn-alt-primary btn-edit-join" data-join='@json($j)'>
                                                <i class="fa fa-pen me-1"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-xs btn-alt-danger btn-delete-join" data-id="{{ $j->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No join relationships registered yet.
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
</div>

<!-- ======================================================== -->
<!-- MODAL: EDIT DATA DICTIONARY FIELD                       -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-edit-field" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-pen-to-square text-primary me-2"></i> Edit Column Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-field-id">

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-xs text-muted mb-0">Table Name</label>
                        <input type="text" class="form-control form-control-sm bg-light font-monospace" id="edit-table-name" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-xs text-muted mb-0">Column Name</label>
                        <input type="text" class="form-control form-control-sm bg-light font-monospace" id="edit-column-name" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold" for="edit-display-name">Display Name / Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit-display-name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold" for="edit-description">Description / Tooltip</label>
                    <textarea class="form-control" id="edit-description" rows="2"></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-8">
                        <label class="form-label fs-sm fw-semibold" for="edit-module-name">Module Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-module-name" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label fs-sm fw-semibold" for="edit-sort-order">Sort Order</label>
                        <input type="number" class="form-control" id="edit-sort-order" value="0">
                    </div>
                </div>

                <div class="border rounded p-3 bg-light">
                    <label class="form-label fs-sm fw-semibold d-block mb-2">Query Engine Capabilities</label>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="edit-is-reportable" checked>
                        <label class="form-check-label fs-sm" for="edit-is-reportable">
                            <strong>Reportable</strong> — Column can be selected in Custom Reports
                        </label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="edit-is-filterable" checked>
                        <label class="form-check-label fs-sm" for="edit-is-filterable">
                            <strong>Filterable</strong> — Column can be filtered in WHERE conditions
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit-is-aggregatable">
                        <label class="form-check-label fs-sm" for="edit-is-aggregatable">
                            <strong>Aggregatable</strong> — Allow SUM(), AVG() math functions
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-field">
                    <i class="fa fa-check me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: ADD / EDIT JOIN RELATIONSHIP                     -->
<!-- ======================================================== -->
<div class="modal fade" id="modal-join" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="join-modal-title">
                    <i class="fa fa-link text-primary me-2"></i> Join Relationship
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="join-id-input">

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-from-table">From Table <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="join-from-table" placeholder="e.g. properties_floors" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-from-col">From Column (FK) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="join-from-col" placeholder="e.g. building_id" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-to-table">To Table (Target) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="join-to-table" placeholder="e.g. properties_building" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-to-col">To Column (PK) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="join-to-col" value="id" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-type-select">Join Type</label>
                        <select class="form-select" id="join-type-select">
                            <option value="LEFT">LEFT JOIN</option>
                            <option value="INNER">INNER JOIN</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-sm fw-semibold" for="join-priority-input">Priority</label>
                        <input type="number" class="form-control" id="join-priority-input" value="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-sm fw-semibold" for="join-desc-input">Description</label>
                    <input type="text" class="form-control" id="join-desc-input" placeholder="e.g. Floor to Building association">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-join">
                    <i class="fa fa-check me-1"></i> Save Join
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Core DataTables -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <!-- Buttons Core + BS5 Integration -->
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>

    <!-- Select2 Plugin -->
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // -------------------------------------------------------------
    // Global DataTable Styling Configuration matching Admin360
    // -------------------------------------------------------------
    jQuery.extend(jQuery.fn.DataTable.ext.classes, {
        sWrapper: "dataTables_wrapper dt-bootstrap5",
        sFilterInput: "form-control form-control-sm",
        sLengthSelect: "form-select form-select-sm"
    });

    jQuery.extend(true, jQuery.fn.DataTable.defaults, {
        language: {
            lengthMenu: "_MENU_",
            search: "_INPUT_",
            searchPlaceholder: "Search columns, tables, labels...",
            info: "Showing <strong>_START_</strong> to <strong>_END_</strong> of <strong>_TOTAL_</strong> columns",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            },
        },
    });

    // -------------------------------------------------------------
    // Initialize Data Dictionary DataTable (Server-Side Yajra)
    // -------------------------------------------------------------
    const dictionaryTable = $("#dictionary-table").DataTable({
        ajax: {
            url: '{{ route('reports.dictionary') }}',
            data: function(d) {
                d.module_name = $('#filter-module').val();
                d.table_name = $('#filter-table').val();
            }
        },
        processing: true,
        serverSide: true,
        autoWidth: false,
        responsive: true,
        pagingType: "full_numbers",
        pageLength: 20,
        lengthMenu: [
            [10, 20, 50, 100],
            [10, 20, 50, 100],
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'id',
                className: 'text-center text-muted',
                orderable: false,
                searchable: false
            },
            {
                data: 'module_badge',
                name: 'module_name'
            },
            {
                data: 'table_col',
                name: 'column_name'
            },
            {
                data: 'display_label',
                name: 'display_name'
            },
            {
                data: 'data_type_badge',
                name: 'data_type',
                className: 'text-center'
            },
            {
                data: 'reportable_badge',
                name: 'is_reportable',
                className: 'text-center'
            },
            {
                data: 'filterable_badge',
                name: 'is_filterable',
                className: 'text-center'
            },
            {
                data: 'aggregatable_badge',
                name: 'is_aggregatable',
                className: 'text-center'
            },
            {
                data: 'actions',
                name: 'actions',
                className: 'text-end',
                orderable: false,
                searchable: false
            }
        ],
        drawCallback: function(settings) {
            // Update the count badge on the tab with the real total count
            if (settings.json && settings.json.recordsTotal !== undefined) {
                $('#total-columns-count').text(settings.json.recordsTotal);
            }
        }
    });

    // Initialize Select2 on Filters and Modals
    $('#filter-module').select2({
        width: '100%',
        placeholder: '-- All Modules --'
    });
    $('#filter-table').select2({
        width: '100%',
        placeholder: '-- All Tables --'
    });
    $('#join-type-select').select2({
        width: '100%',
        dropdownParent: $('#modal-join')
    });

    // Handle Dropdown Filters Reload
    $('#filter-module, #filter-table').on('change', function() {
        dictionaryTable.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-module').val('all').trigger('change');
        $('#filter-table').val('all').trigger('change');
    });

    // -------------------------------------------------------------
    // Initialize Joins DataTable
    // -------------------------------------------------------------
    $('#joins-table').DataTable({
        responsive: true,
        autoWidth: false,
        pagingType: "full_numbers",
        pageLength: 15,
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Helper for Toast / Notifications
    function showNotification(type, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'danger' ? 'error' : (type === 'alt-success' ? 'success' : type),
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        } else if (typeof One !== 'undefined' && One.helpers) {
            One.helpers('jq-notify', { type: type, message: message });
        } else {
            alert(message);
        }
    }

    // -------------------------------------------------------------
    // Sync Schema Button
    // -------------------------------------------------------------
    $('#btn-sync-schema').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Introspecting Schema...');

        $.post("{{ route('reports.dictionary.sync') }}", function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-arrows-rotate me-1"></i> Sync Database Schema Now');

            const m = res.metrics || {};
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Synchronization Complete',
                    html: `
                        <div class="text-start fs-sm">
                            <ul class="list-group list-group-flush mb-0">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>New Columns Registered:</span>
                                    <strong>${m.created_columns || 0}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Existing Columns Preserved:</span>
                                    <strong>${m.existing_columns || 0}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Column Types Updated:</span>
                                    <strong>${m.updated_columns || 0}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>New Joins Registered:</span>
                                    <strong>${m.created_joins || 0}</strong>
                                </li>
                            </ul>
                        </div>
                    `
                }).then(() => {
                    dictionaryTable.ajax.reload();
                });
            } else {
                alert('Schema synchronization finished successfully.');
                dictionaryTable.ajax.reload();
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa fa-arrows-rotate me-1"></i> Sync Database Schema Now');
            showNotification('danger', 'Schema synchronization failed.');
        });
    });

    // -------------------------------------------------------------
    // Edit Field Modal
    // -------------------------------------------------------------
    $(document).on('click', '.btn-edit-field', function() {
        const field = $(this).data('field');

        $('#edit-field-id').val(field.id);
        $('#edit-table-name').val(field.table_name);
        $('#edit-column-name').val(field.column_name);
        $('#edit-display-name').val(field.display_name);
        $('#edit-description').val(field.description || '');
        $('#edit-module-name').val(field.module_name);
        $('#edit-sort-order').val(field.sort_order || 0);

        $('#edit-is-reportable').prop('checked', !!field.is_reportable);
        $('#edit-is-filterable').prop('checked', !!field.is_filterable);
        $('#edit-is-aggregatable').prop('checked', !!field.is_aggregatable);

        $('#modal-edit-field').modal('show');
    });

    $('#btn-save-field').on('click', function() {
        const id = $('#edit-field-id').val();
        const payload = {
            display_name: $('#edit-display-name').val().trim(),
            description: $('#edit-description').val().trim(),
            module_name: $('#edit-module-name').val().trim(),
            sort_order: parseInt($('#edit-sort-order').val()) || 0,
            is_reportable: $('#edit-is-reportable').is(':checked') ? 1 : 0,
            is_filterable: $('#edit-is-filterable').is(':checked') ? 1 : 0,
            is_aggregatable: $('#edit-is-aggregatable').is(':checked') ? 1 : 0,
        };

        if (!payload.display_name || !payload.module_name) {
            showNotification('warning', 'Display Name and Module Name are required.');
            return;
        }

        $.post(`{{ url('reports/dictionary') }}/${id}`, payload, function(res) {
            $('#modal-edit-field').modal('hide');
            showNotification('success', 'Field configuration updated successfully.');
            dictionaryTable.ajax.reload(null, false); // Reload without resetting paging
        }).fail(function(xhr) {
            showNotification('danger', xhr.responseJSON?.message || 'Failed to update field.');
        });
    });

    // -------------------------------------------------------------
    // Join Relationships Modal & Actions
    // -------------------------------------------------------------
    $('#btn-add-join').on('click', function() {
        $('#join-id-input').val('');
        $('#join-from-table').val('');
        $('#join-from-col').val('');
        $('#join-to-table').val('');
        $('#join-to-col').val('id');
        $('#join-type-select').val('LEFT');
        $('#join-priority-input').val(0);
        $('#join-desc-input').val('');
        $('#join-modal-title').html('<i class="fa fa-link text-primary me-2"></i> Add Join Relationship');
        $('#modal-join').modal('show');
    });

    $(document).on('click', '.btn-edit-join', function() {
        const join = $(this).data('join');
        $('#join-id-input').val(join.id);
        $('#join-from-table').val(join.from_table);
        $('#join-from-col').val(join.from_column);
        $('#join-to-table').val(join.to_table);
        $('#join-to-col').val(join.to_column);
        $('#join-type-select').val(join.join_type || 'LEFT');
        $('#join-priority-input').val(join.priority || 0);
        $('#join-desc-input').val(join.description || '');
        $('#join-modal-title').html('<i class="fa fa-pen text-primary me-2"></i> Edit Join Relationship');
        $('#modal-join').modal('show');
    });

    $('#btn-save-join').on('click', function() {
        const payload = {
            join_id: $('#join-id-input').val() || null,
            from_table: $('#join-from-table').val().trim(),
            from_column: $('#join-from-col').val().trim(),
            to_table: $('#join-to-table').val().trim(),
            to_column: $('#join-to-col').val().trim(),
            join_type: $('#join-type-select').val(),
            priority: parseInt($('#join-priority-input').val()) || 0,
            description: $('#join-desc-input').val().trim()
        };

        if (!payload.from_table || !payload.from_column || !payload.to_table || !payload.to_column) {
            showNotification('warning', 'All table and column fields are required.');
            return;
        }

        $.post("{{ route('reports.joins.save') }}", payload, function(res) {
            $('#modal-join').modal('hide');
            showNotification('success', res.message || 'Join relationship saved.');
            setTimeout(() => location.reload(), 600);
        }).fail(function(xhr) {
            showNotification('danger', xhr.responseJSON?.message || 'Failed to save join relationship.');
        });
    });

    $(document).on('click', '.btn-delete-join', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');

        const doDelete = function() {
            $.ajax({
                url: `{{ url('reports/joins') }}/${id}`,
                type: 'DELETE',
                success: function() {
                    row.fadeOut(300, function() { $(this).remove(); });
                    showNotification('success', 'Join relationship removed.');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Join Relationship?',
                text: 'This join path will no longer be available for the report query builder.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it'
            }).then((res) => {
                if (res.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm('Delete this join relationship?')) {
                doDelete();
            }
        }
    });
});
</script>
@endsection
