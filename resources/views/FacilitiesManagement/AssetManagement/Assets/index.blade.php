@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ config('app.name') }} | Assets
@endsection

@section('page_title')
    Asset List
@endsection

@section('styles')
    <!-- DataTables & Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.bootstrap5.min.css">

    <style>
        /* Highlight the active card filter */
        .category-filter.active {
            border: 2px solid #0665d0 !important;
            box-shadow: 0 0 10px rgba(6, 101, 208, 0.2);
        }

        .category-filter {
            border: 2px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        /* Modal Sidebar Style */
        .modal-dialog-right {
            margin-right: 0;
            margin-top: 0;
            margin-bottom: 0;
            height: 100vh;
        }

        .modal-dialog-right .modal-content {
            height: 100vh;
            border-radius: 0;
        }

        /* Ensure switches look clean */
        .form-check-input:checked {
            background-color: #0665d0;
            border-color: #0665d0;
        }

        /* Fix Table width issues */
        #assets-table {
            width: 100% !important;
            min-width: 1500px;
        }

        .dataTables_scrollBody {
            border-bottom: none !important;
        }

        #assets-table th,
        #assets-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        #assets-table th.asset-status-column,
        #assets-table td.asset-status-column {
            min-width: 90px;
            text-align: center;
        }

        #assets-table th.asset-actions-column,
        #assets-table td.asset-actions-column {
            min-width: 118px;
            text-align: center;
        }

        .dataTables_wrapper .dataTables_scroll {
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            overflow: hidden;
        }

        .dataTables_wrapper .dataTables_scrollHead table,
        .dataTables_wrapper .dataTables_scrollBody table {
            margin-bottom: 0 !important;
        }

        .dataTables_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <!-- Filter Cards -->
        <div class="row items-push">
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter active" href="javascript:void(0)"
                    data-category-id="all">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-dark">All Assets</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="Air Conditioner">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-primary">Air Conditioner</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="Fire Extinguisher">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-success">Fire Extinguisher</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="Aircon Timer">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-warning">Aircon Timer</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Assets Management</h3>
                <a href="{{ route('assets.create') }}" id="btn-add-asset" class="btn btn-sm btn-primary">Add Asset</a>
            </div>
            <div class="block-content fs-sm">
                <div class="table-responsive">
                    <table id="assets-table"
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover">
                        <thead>
                            <!-- Thead will be auto-populated by DataTables via 'title' attributes in JS -->
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Column Settings Modal (Standard Bootstrap 5 Modal) -->
    @include('Partials.column_settings_modal')
@endsection

@section('scripts')
    <!-- Core JS -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script>

    <script>
        $(function() {
            let table = null;
            let selectedCategoryId = 'all'; // Track currently selected category

            // 1. Permissions and Database Config
            const isAdmin = @json(auth()->user()->hasRole('Super Admin'));
            const globalSettingsMap = @json($assetSettings); // Map of all category settings

            const baseColumns = [{
                    data: 'id',
                    name: 'id',
                    title: 'ID'
                },
                {
                    data: 'site_code',
                    name: 'site_code',
                    title: 'Site Code'
                },
                {
                    data: 'building_floor',
                    name: 'building_floor',
                    title: 'Site Name'
                },
                {
                    data: 'asset_tag',
                    name: 'asset_tag',
                    title: 'Unique Code'
                },
                {
                    data: 'floor',
                    name: 'floor',
                    title: 'Floor'
                },
                {
                    data: 'subcenter',
                    name: 'subcenter',
                    title: 'Subcenter'
                },
                {
                    data: 'location_within_floor',
                    name: 'location_within_floor',
                    title: 'Location'
                },
                {
                    data: 'category',
                    name: 'category',
                    title: 'Category'
                },
                {
                    data: 'brand',
                    name: 'brand',
                    title: 'Brand'
                },
                {
                    data: 'model',
                    name: 'model',
                    title: 'Model'
                },
                {
                    data: 'serial_number',
                    name: 'serial_number',
                    title: 'Serial No'
                },
                {
                    data: 'purchase_date',
                    name: 'purchase_date',
                    title: 'Purchase Date'
                },
                {
                    data: 'warranty_expiry',
                    name: 'warranty_expiry',
                    title: 'Warranty'
                },
                {
                    data: 'parent',
                    name: 'parent',
                    title: 'Parent'
                },
                {
                    data: 'project',
                    name: 'project',
                    title: 'Project'
                },
                {
                    data: 'status',
                    name: 'status',
                    title: 'Status',
                    className: 'asset-status-column'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    className: 'asset-actions-column'
                }
            ];

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function resetTableMarkup(columns) {
                const headerCells = columns
                    .map(column => `<th>${escapeHtml(column.title || '')}</th>`)
                    .join('');

                $('#assets-table')
                    .empty()
                    .append(`<thead><tr>${headerCells}</tr></thead><tbody></tbody>`);
            }

            function columnSignature(columns) {
                return columns.map(column => column.name || column.data || '').join('|');
            }

            function validSavedState(savedState, columns) {
                return savedState &&
                    Array.isArray(savedState.columns) &&
                    savedState.columns.length === columns.length &&
                    savedState.assetColumnSignature === columnSignature(columns);
            }

            function refreshTableWithDynamicColumns(categoryId) {
                selectedCategoryId = categoryId; // Update global tracker

                if ($.fn.DataTable.isDataTable('#assets-table')) {
                    table.destroy();
                    resetTableMarkup(baseColumns);
                } else {
                    $('#assets-table tbody').empty();
                }

                $.ajax({
                    url: '{{ route('assets.index') }}',
                    data: {
                        category_id: categoryId,
                        draw: 1,
                        length: 1
                    },
                    success: function(response) {
                        let columns = [...baseColumns];

                        if (response.dynamic_attributes && response.dynamic_attributes.length > 0) {
                            response.dynamic_attributes.forEach(attr => {
                                columns.splice(columns.length - 1, 0, {
                                    data: 'attr_' + attr.id,
                                    name: 'attr_' + attr.id,
                                    title: attr.attribute_name,
                                    searchable: false,
                                    visible: true
                                });
                            });
                        }

                        resetTableMarkup(columns);
                        const assetColumnSignature = columnSignature(columns);
                        const statusColumnIndex = columns.findIndex(column => column.name === 'status');
                        const actionsColumnIndex = columns.findIndex(column => column.name === 'actions');

                        table = $('#assets-table').DataTable({
                            processing: true,
                            serverSide: true,
                            stateDuration: -1,

                            // 2. Admin Reorder Control
                            colReorder: isAdmin ? {
                                fixedColumnsLeft: 1,
                                fixedColumnsRight: 1
                            } : false,

                            // 3. Load Layout from Database based on Category
                            stateSave: true,
                            stateSaveParams: function(settings, data) {
                                data.assetColumnSignature = assetColumnSignature;
                            },
                            stateLoadCallback: function(settings) {
                                const key = 'assets_table_' + categoryId;
                                try {
                                    const savedState = globalSettingsMap[key] ? JSON.parse(globalSettingsMap[key]) : null;

                                    if (!validSavedState(savedState, columns)) {
                                        return null;
                                    }

                                    savedState.columns[actionsColumnIndex].visible = true;
                                    return savedState;
                                } catch (e) {
                                    return null;
                                }
                            },

                            scrollX: true,
                            scrollCollapse: true,
                            autoWidth: false,
                            ajax: {
                                url: '{{ route('assets.index') }}',
                                data: {
                                    category_id: categoryId
                                }
                            },
                            columns: columns,
                            columnDefs: [
                                {
                                    targets: statusColumnIndex,
                                    className: 'asset-status-column',
                                    width: '90px'
                                },
                                {
                                    targets: actionsColumnIndex,
                                    visible: true,
                                    orderable: false,
                                    searchable: false,
                                    className: 'asset-actions-column',
                                    width: '118px'
                                }
                            ],
                            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'d-flex justify-content-end gap-2'Bf>>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                            // 4. Admin Settings Button
                            buttons: isAdmin ? [{
                                text: '<i class="fa fa-cog me-1"></i> Column Settings',
                                className: 'btn btn-sm btn-alt-secondary',
                                action: function() {
                                    buildSettingsModal(table);
                                    $('#modal-column-settings').modal('show');
                                }
                            }] : [],

                            initComplete: function() {
                                this.api().settings()[0]._assetColumnSignature = assetColumnSignature;

                                if (isAdmin) {
                                    buildSettingsModal(this.api());
                                    injectSaveButton();
                                }
                            }
                        });
                    },
                    error: function() {
                        if ($.fn.DataTable.isDataTable('#assets-table')) {
                            table.destroy();
                        }

                        resetTableMarkup(baseColumns);
                    }
                });
            }

            function buildSettingsModal(dtInstance) {
                const container = $('#column-toggle-container');
                container.empty();
                dtInstance.columns().every(function(index) {
                    const column = this;
                    const title = dtInstance.settings()[0].aoColumns[index].sTitle;
                    if (title === 'ID' || title === 'Actions' || title === 'SI' || !title) return;

                    const isChecked = column.visible() ? 'checked' : '';
                    const safeTitle = escapeHtml(title);
                    const switchHtml = `
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input col-toggle-input" type="checkbox" 
                                   id="asset_col_${index}" data-column="${index}" ${isChecked}>
                            <label class="form-check-label fw-medium" for="asset_col_${index}">${safeTitle}</label>
                        </div>`;
                    container.append(switchHtml);
                });
            }

            // Admin only: Save Global Config for this Category
            function injectSaveButton() {
                if ($('#btn-save-global').length === 0) {
                    $('<button type="button" id="btn-save-global" class="btn btn-alt-success me-1">Save for All Users</button>')
                        .prependTo('#modal-column-settings .modal-content .block-content-full')
                        .on('click', function() {
                            const state = table.state();
                            state.assetColumnSignature = table.settings()[0]._assetColumnSignature;
                            $.ajax({
                                url: '{{ route('table_settings.save') }}',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    table_identifier: 'assets_table_' + selectedCategoryId,
                                    settings: JSON.stringify(state)
                                },
                                success: function() {
                                    alert('Layout for ' + selectedCategoryId +
                                        ' saved for all users!');
                                    window.location.reload();
                                }
                            });
                        });
                }
            }

            $(document).on('change', '.col-toggle-input', function() {
                const columnIdx = $(this).data('column');
                table.column(columnIdx).visible(this.checked);
            });

            $('#btn-reset-layout').on('click', function() {
                // To reset, we send a request to delete the DB entry or just clear local
                table.state.clear();
                window.location.reload();
            });

            $('.category-filter').on('click', function() {
                $('.category-filter').removeClass('active');
                $(this).addClass('active');

                const categoryId = $(this).data('category-id');

                // UPDATE: Change the Add Asset button URL
                let createUrl = "{{ route('assets.create') }}";
                if (categoryId !== 'all') {
                    createUrl += "?category=" + encodeURIComponent(categoryId);
                }
                $('#btn-add-asset').attr('href', createUrl);

                refreshTableWithDynamicColumns(categoryId);
            });

            refreshTableWithDynamicColumns('all');
        });
    </script>
@endsection
