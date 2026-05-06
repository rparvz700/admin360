@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Floor List
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.bootstrap5.min.css">

    <link rel="stylesheet" href="{{ asset('css/column_settings.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="floor-page-header">
            <div>
                <div class="floor-eyebrow">Facilities Management</div>
                <h2>Floors</h2>
                <p>Manage floor inventory, assignment, and occupancy-related references.</p>
            </div>
            <a href="{{ route('floors.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Add Floor
            </a>
        </div>

        <div class="block block-rounded floor-shell">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible m-3 mb-0" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="block-header block-header-default floor-block-header">
                <div>
                    <h3 class="block-title">Floor Directory</h3>
                    <div class="text-muted fs-sm">Search, reorder, and configure visible floor columns.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-vcenter table-hover floor-table js-dataTable-full js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Site Code</th>
                                <th>Building</th>
                                <th>Floor Label</th>
                                <th>Area (sft)</th>
                                <th>Status</th>
                                <th>Agreement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Column Settings Modal -->
    @include('Partials.column_settings_modal')
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

    <!-- Buttons Extensions -->
    <script src="{{ asset('js/plugins/datatables-buttons/buttons.colVis.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script>

    <script>
        !(function() {
            // 1. Permissions and Database Config
            const isAdmin = @json(auth()->user()->hasRole('Super Admin'));
            const globalConfig = @json($tableConfig);

            class FloorTable {
                static init() {
                    const tableElement = $(".js-dataTable-responsive");

                    let dt = tableElement.DataTable({
                        ajax: '{{ route('floors.list') }}',
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,

                        // 2. Only Admin can reorder
                        colReorder: isAdmin,

                        // 3. Load Layout from Database (Global for everyone)
                        stateSave: true,
                        stateDuration: -1,
                        stateLoadCallback: function(settings) {
                            try {
                                return JSON.parse(globalConfig);
                            } catch (e) {
                                return null;
                            }
                        },

                        pagingType: "full_numbers",
                        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'d-flex justify-content-end gap-2'Bf>>>" +
                            "<'row'<'col-sm-12'tr>>" +
                            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

                        // 4. Settings button only for Admin
                        buttons: isAdmin ? [{
                            text: '<i class="fa fa-cog me-1"></i> Column Settings',
                            className: 'btn btn-sm btn-alt-secondary',
                            action: function(e, dt, node, config) {
                                $('#modal-column-settings').modal('show');
                            }
                        }] : [],

                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'SI',
                                className: 'text-center text-muted',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'code',
                                name: 'code'
                            },
                            {
                                data: 'building'
                            },
                            {
                                data: 'floor_label'
                            },
                            {
                                data: 'floor_area_sft'
                            },
                            {
                                data: 'status'
                            },
                            {
                                data: 'agreement'
                            },
                            {
                                data: 'actions',
                                className: 'text-end',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        initComplete: function() {
                            if (isAdmin) {
                                buildSettingsModal(this.api());
                                injectSaveButton();
                            }
                        }
                    });

                    // Build switches for Admin
                    function buildSettingsModal(api) {
                        const container = $('#column-toggle-container');
                        container.empty();

                        api.columns().every(function(index) {
                            const column = this;
                            const title = $(column.header()).text().trim();

                            // Filter out SI and Actions
                            if (title === 'SI' || title === 'Actions' || title === '' || title === 'ID')
                                return;

                            const isChecked = column.visible() ? 'checked' : '';
                            const switchHtml = `
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input col-toggle-input" type="checkbox" 
                                           id="fl_col_${index}" data-column="${index}" ${isChecked}>
                                    <label class="form-check-label fw-medium" for="fl_col_${index}">${title}</label>
                                </div>`;
                            container.append(switchHtml);
                        });
                    }

                    // Admin only: Add Global Save button to Modal
                    function injectSaveButton() {
                        if ($('#btn-save-global').length === 0) {
                            $('<button type="button" id="btn-save-global" class="btn btn-alt-success me-1">Save for All Users</button>')
                                .prependTo('#modal-column-settings .modal-content .block-content-full')
                                .on('click', function() {
                                    const state = dt.state();
                                    $.ajax({
                                        url: '{{ route('table_settings.save') }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            table_identifier: 'floors_table',
                                            settings: JSON.stringify(state)
                                        },
                                        success: function() {
                                            alert('Floor layout saved globally!');
                                            window.location.reload();
                                        },
                                        error: function() {
                                            alert('Failed to save layout.');
                                        }
                                    });
                                });
                        }
                    }

                    // Admin: Handle visibility toggles
                    $(document).on('change', '.col-toggle-input', function() {
                        const columnIdx = $(this).data('column');
                        dt.column(columnIdx).visible(this.checked);
                    });

                    // Admin: Handle Reset
                    $('#btn-reset-layout').on('click', function() {
                        dt.state.clear();
                        window.location.reload();
                    });
                }
            }
            $(document).ready(() => FloorTable.init());
        })();
    </script>
@endsection
