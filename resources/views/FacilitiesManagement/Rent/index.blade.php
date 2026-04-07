@extends('Partials.app', ['activeMenu' => 'rent'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Rent List
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
        <div class="block block-rounded">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="block-header block-header-default">
                <h3 class="block-title">Rent</h3>
                <a href="{{ route('rent.create') }}" class="btn btn-sm btn-primary">Add Rent</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Agreement</th>
                                <th>Base Rent</th>
                                <th>Agreement Start Date</th>
                                <th>Agreement End Date</th>
                                <th>Status</th>
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

            class RentTable {
                static init() {
                    const tableElement = $(".js-dataTable-responsive");

                    let dt = tableElement.DataTable({
                        ajax: '{{ route('rent.list') }}',
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,

                        // 2. Only Admin can reorder columns
                        colReorder: isAdmin,

                        // 3. Load Layout from Database (Enforced for all users)
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

                        // 4. Settings button only visible to Admin
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
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'agreement'
                            },
                            {
                                data: 'base_rent'
                            },
                            {
                                data: 'agreement_start_date'
                            },
                            {
                                data: 'agreement_end_date'
                            },
                            {
                                data: 'status',
                                searchable: false,
                                orderable: false
                            },
                            {
                                data: 'actions',
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

                    // Build switches for the Admin Modal
                    function buildSettingsModal(api) {
                        const container = $('#column-toggle-container');
                        container.empty();

                        api.columns().every(function(index) {
                            const column = this;
                            const title = $(column.header()).text().trim();

                            // Protect SI and Actions from being toggled
                            if (title === 'SI' || title === 'Actions' || title === '' || title === 'ID')
                                return;

                            const isChecked = column.visible() ? 'checked' : '';
                            const switchHtml = `
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input col-toggle-input" type="checkbox" 
                                           id="rt_col_${index}" data-column="${index}" ${isChecked}>
                                    <label class="form-check-label fw-medium" for="rt_col_${index}">${title}</label>
                                </div>`;
                            container.append(switchHtml);
                        });
                    }

                    // Admin only: Function to inject the Save Global button
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
                                            table_identifier: 'rent_table',
                                            settings: JSON.stringify(state)
                                        },
                                        success: function() {
                                            alert('Rent layout saved for all users successfully!');
                                            window.location.reload();
                                        },
                                        error: function() {
                                            alert('Failed to save layout configuration.');
                                        }
                                    });
                                });
                        }
                    }

                    // Admin only: Handle visibility switches
                    $(document).on('change', '.col-toggle-input', function() {
                        const columnIdx = $(this).data('column');
                        dt.column(columnIdx).visible(this.checked);
                    });

                    // Admin only: Reset the layout
                    $('#btn-reset-layout').on('click', function() {
                        dt.state.clear();
                        window.location.reload();
                    });
                }
            }
            $(document).ready(() => RentTable.init());
        })();
    </script>
@endsection
