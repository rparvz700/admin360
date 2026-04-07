@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Building List
@endsection

@section('styles')
    <!-- Page JS Plugins CSS for datatable -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.bootstrap5.min.css">

    <link rel="stylesheet" href="{{ asset('css/column_settings.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            {{-- Response message --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            {{-- End response message --}}
            <div class="block-header block-header-default">
                <h3 class="block-title">Buildings</h3>
                @can('create-building')
                    <a href="{{ route('buildings.create') }}" class="btn btn-sm btn-primary">Add Building</a>
                @endcan
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th class="text-center all">ID</th>
                                <th class="all">Code</th>
                                <th class="all">Name</th>
                                <th class="all">Division</th>
                                <th class="all">District</th>
                                <th class="all">Upazila</th>
                                <th class="all">Area</th>
                                <th class="all">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->

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
            // 1. Configuration & Permissions
            const isAdmin = @json(auth()->user()->hasRole('Super Admin')); // Check Admin status
            const globalConfig = @json($tableConfig); // Settings from Database

            class BuildingTable {
                static init() {
                    const tableElement = $(".js-dataTable-responsive");

                    let dt = tableElement.DataTable({
                        ajax: '{{ route('buildings.list') }}',
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,

                        // 2. Only Admin can drag/reorder columns
                        colReorder: isAdmin,

                        // 3. Load Layout from Database for all users
                        stateSave: true,
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

                        // 4. Only show Settings Button to Admin
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
                                data: 'code'
                            },
                            {
                                data: 'site_name'
                            },
                            {
                                data: 'division'
                            },
                            {
                                data: 'district'
                            },
                            {
                                data: 'upazila'
                            },
                            {
                                data: 'area'
                            },
                            {
                                data: 'actions',
                                searchable: false
                            }
                        ],
                        initComplete: function() {
                            // Only build modal and save button if user is Admin
                            if (isAdmin) {
                                buildSettingsModal(this.api());
                                injectSaveButton();
                            }
                        }
                    });

                    // Build switches for Admin modal
                    function buildSettingsModal(api) {
                        const container = $('#column-toggle-container');
                        container.empty();

                        api.columns().every(function(index) {
                            const column = this;
                            const title = $(column.header()).text().trim();

                            // Protection for fixed columns
                            if (title === 'SI' || title === 'Actions' || title === '' || title === 'ID')
                                return;

                            const isChecked = column.visible() ? 'checked' : '';

                            const switchHtml = `
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input col-toggle-input" type="checkbox" 
                                           id="bt_col_${index}" data-column="${index}" ${isChecked}>
                                    <label class="form-check-label fw-medium" for="bt_col_${index}">${title}</label>
                                </div>`;
                            container.append(switchHtml);
                        });
                    }

                    // Admin only: Add "Save for All" button to modal
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
                                            table_identifier: 'buildings_table',
                                            settings: JSON.stringify(state)
                                        },
                                        success: function() {
                                            alert('Building layout saved for all users!');
                                            window.location.reload();
                                        },
                                        error: function() {
                                            alert('Error saving table configuration.');
                                        }
                                    });
                                });
                        }
                    }

                    // Admin: Handle Switch Toggles
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
            $(document).ready(() => BuildingTable.init());
        })();

        $(document).on('click', '.delete-button', function() {
            var buildingId = $(this).data('building-id');
            if (confirm('Do you want to delete this building?')) {
                $('#deleteForm' + buildingId).submit();
            }
        });
    </script>
@endsection
