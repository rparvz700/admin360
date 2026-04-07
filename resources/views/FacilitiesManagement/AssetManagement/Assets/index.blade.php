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
        }

        .dataTables_scrollBody {
            border-bottom: none !important;
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
                <a href="{{ route('assets.create') }}" class="btn btn-sm btn-primary">Add Asset</a>
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
    <div class="modal fade" id="modal-column-settings" tabindex="-1" role="dialog" aria-labelledby="modal-column-settings"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-right" role="document">
            <div class="modal-content">
                <div class="block block-rounded shadow-none mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Display Columns</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content overflow-auto" style="max-height: 80vh;">
                        <div id="column-toggle-container">
                            <!-- Switches injected here via JS -->
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" class="btn btn-alt-secondary" id="btn-reset-layout">Reset Layout</button>
                        <button type="button" class="btn btn-alt-primary" data-bs-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

            // 1. Define Static Base Columns
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
                    title: 'Status'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    title: 'Actions',
                    orderable: false,
                    searchable: false
                }
            ];

            // 2. Function to Initialize/Rebuild DataTable
            function refreshTableWithDynamicColumns(categoryId) {
                $.ajax({
                    url: '{{ route('assets.index') }}',
                    data: {
                        category_id: categoryId,
                        draw: 1,
                        length: 1
                    },
                    success: function(response) {
                        let columns = [...baseColumns];

                        // 1. Inject dynamic attributes
                        if (response.dynamic_attributes && response.dynamic_attributes.length > 0) {
                            response.dynamic_attributes.forEach(attr => {
                                columns.splice(columns.length - 1, 0, {
                                    data: 'attr_' + attr.id,
                                    name: 'attr_' + attr.id,
                                    title: attr.attribute_name,
                                    searchable: false,
                                    visible: true // Ensure they are visible by default
                                });
                            });
                        }

                        // 2. Destroy previous instance and CLEAN the table
                        if ($.fn.DataTable.isDataTable('#assets-table')) {
                            table.destroy();
                            $('#assets-table').empty();
                        }

                        // 3. Re-initialize
                        table = $('#assets-table').DataTable({
                            processing: true,
                            serverSide: true,

                            colReorder: {
                                fixedColumnsLeft: 1 // Keeps the 'SI' column from being moved
                            },
                            stateSave: true,

                            stateSaveCallback: function(settings, data) {
                                localStorage.setItem('DataTables_Assets_' + categoryId, JSON
                                    .stringify(data));
                            },
                            stateLoadCallback: function(settings) {
                                return JSON.parse(localStorage.getItem(
                                    'DataTables_Assets_' + categoryId));
                            },

                            scrollX: true,
                            autoWidth: false,
                            ajax: {
                                url: '{{ route('assets.index') }}',
                                data: {
                                    category_id: categoryId
                                }
                            },
                            columns: columns,
                            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'d-flex justify-content-end gap-2'Bf>>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                            buttons: [{
                                text: '<i class="fa fa-cog me-1"></i> Column Settings',
                                className: 'btn btn-sm btn-alt-secondary',
                                action: function() {
                                    // Build the modal logic right before showing it
                                    buildSettingsModal(table);
                                    $('#modal-column-settings').modal('show');
                                }
                            }],
                            initComplete: function() {
                                // Force the modal switches to match the loaded state
                                buildSettingsModal(this.api());
                            }
                        });
                    }
                });
            }

            // 3. Populate Settings Modal with switches
            function buildSettingsModal(dtInstance) {
                const container = $('#column-toggle-container');
                container.empty();

                // Get all columns from the instance
                dtInstance.columns().every(function(index) {
                    const column = this;
                    // Use settings to get the title defined in JS, more reliable than header text
                    const title = dtInstance.settings()[0].aoColumns[index].sTitle;

                    // Skip ID and Actions
                    if (title === 'ID' || title === 'Actions' || title === 'SI' || !title) return;

                    const isChecked = column.visible() ? 'checked' : '';
                    const switchHtml = `
            <div class="form-check form-switch mb-3">
                <input class="form-check-input col-toggle-input" type="checkbox" 
                       id="asset_col_${index}" data-column="${index}" ${isChecked}>
                <label class="form-check-label fw-medium" for="asset_col_${index}">${title}</label>
            </div>`;
                    container.append(switchHtml);
                });
            }

            // 4. Handle Show/Hide Switch Events
            $(document).on('change', '.col-toggle-input', function() {
                const columnIdx = $(this).data('column');
                table.column(columnIdx).visible(this.checked);
            });

            // 5. Handle Reset Layout
            $('#btn-reset-layout').on('click', function() {
                // Remove the setup for the currently active category only
                localStorage.removeItem('DataTables_Assets_' + selectedCategoryId);
                window.location.reload();
            });

            // 6. Filter Card Click Events
            $('.category-filter').on('click', function() {
                $('.category-filter').removeClass('active');
                $(this).addClass('active');

                const categoryId = $(this).data('category-id');
                refreshTableWithDynamicColumns(categoryId);
            });

            // Initial Load
            refreshTableWithDynamicColumns('all');
        });
    </script>
@endsection
