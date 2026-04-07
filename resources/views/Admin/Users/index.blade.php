@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ config('app.name') }}
@endsection


@section('page_title')
    User List
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
    <!-- Hero -->
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
                <h3 class="block-title">Users</h3>
                @can('create-user')
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">Add User</a>
                @endcan
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th class="text-center all">SI</th>
                                <th class="all">Name</th>
                                <th class="all">Email</th>
                                <th class="">Role</th>
                                <th class="all">Status</th>
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
            class UserTable {
                static init() {
                    const tableElement = jQuery(".js-dataTable-responsive");

                    let dt = tableElement.DataTable({
                        ajax: '{{ $listRoute }}',
                        processing: true,
                        serverSide: true,
                        colReorder: true,
                        stateSave: true,
                        autoWidth: false,
                        responsive: true,
                        pagingType: "full_numbers",
                        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'d-flex justify-content-end gap-2'Bf>>>" +
                            "<'row'<'col-sm-12'tr>>" +
                            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                        buttons: [{
                            text: '<i class="fa fa-cog me-1"></i> Column Settings',
                            className: 'btn btn-sm btn-alt-secondary',
                            action: function(e, dt, node, config) {
                                $('#modal-column-settings').modal('show');
                            }
                        }],
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'SI',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                name: 'Name'
                            },
                            {
                                data: 'email',
                                name: 'Email'
                            },
                            {
                                data: 'roles',
                                name: 'Roles',
                                searchable: false
                            },
                            {
                                data: 'status',
                                name: 'Status'
                            },
                            {
                                data: 'actions',
                                name: 'Actions',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        // Build the settings list when the table is ready
                        initComplete: function() {
                            const api = this.api();
                            const container = $('#column-toggle-container');
                            container.empty();

                            api.columns().every(function(index) {
                                const column = this;
                                const title = $(column.header()).text().trim();

                                // Don't allow hiding SI or Actions
                                if (title === 'SI' || title === 'Actions' || title === '')
                                    return;

                                const isChecked = column.visible() ? 'checked' : '';

                                const switchHtml = `
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input col-toggle-input" type="checkbox" 
                                           id="col_toggle_${index}" data-column="${index}" ${isChecked}>
                                    <label class="form-check-label" for="col_toggle_${index}">${title}</label>
                                </div>`;
                                container.append(switchHtml);
                            });
                        }
                    });

                    // Handle Toggle Clicks
                    $(document).on('change', '.col-toggle-input', function() {
                        const columnIdx = $(this).data('column');
                        dt.column(columnIdx).visible(this.checked);
                    });

                    // Handle Reset
                    $('#btn-reset-layout').on('click', function() {
                        dt.state.clear();
                        window.location.reload();
                    });
                }
            }
            $(document).ready(() => UserTable.init());
        })();

        // Delete confirmation
        $(document).on('click', '.delete-button', function() {
            var userId = $(this).data('user-id');
            if (confirm('Do you want to delete this user?')) {
                $('#deleteForm' + userId).submit();
            }
        });
    </script>
@endsection
