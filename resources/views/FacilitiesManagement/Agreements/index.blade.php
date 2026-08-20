@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Agreement List
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
        <div class="agreement-page-header">
            <div>
                <div class="agreement-eyebrow">Facilities Management</div>
                <h2>Agreements</h2>
                <p>Manage agreement references, validity dates, status, and supporting remarks.</p>
            </div>
            <a href="{{ route('agreements.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Add Agreement
            </a>
        </div>

        <div class="block block-rounded agreement-shell">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible m-3 mb-0" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (Session::has('error'))
                <div class="alert alert-danger alert-dismissible m-3 mb-0" role="alert">
                    <small class="mb-0">
                        {{ Session::get('error') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="block-header block-header-default agreement-block-header">
                <div>
                    <h3 class="block-title">Agreement Directory</h3>
                    <div class="text-muted fs-sm">Search, reorder, and configure visible agreement columns.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="agreements-table"
                        class="table table-sm table-vcenter table-hover agreement-table js-dataTable-full js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th class="text-center all">ID</th>
                                <th class="all">Reference No</th>
                                <th class="all">Vendor</th>
                                <th class="all">Agreement Date</th>
                                <th class="all">Payment Start Date</th>
                                <th class="all">Expiry Date</th>
                                <th class="all">Status</th>
                                <th class="all">Remarks</th>
                                <th class="all">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
            // 1. Check permissions and load database config
            const isAdmin = @json(auth()->user()->hasRole('Super Admin')); // Adjust role name as needed
            const globalConfig = @json($tableConfig);

            class AgreementTable {
                static init() {
                    const tableElement = $("#agreements-table");

                    let dt = tableElement.DataTable({
                        ajax: '{{ route('agreements.index') }}',
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,

                        // 2. Only Admin can reorder
                        colReorder: isAdmin,

                        // 3. Load layout from Database
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

                        // 4. Only show the Settings button to Admin
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
                                data: 'agreement_ref_no',
                                name: 'agreement_ref_no'
                            },
                            {
                                data: 'vendor',
                                name: 'vendor'
                            },
                            {
                                data: 'agreement_date',
                                name: 'agreement_date'
                            },
                            {
                                data: 'payment_start_date',
                                name: 'payment_start_date',
                                defaultContent: ''
                            },
                            {
                                data: 'expiry_date',
                                name: 'expiry_date',
                                defaultContent: ''
                            },
                            {
                                data: 'status',
                                name: 'status',
                                className: 'text-center'
                            },
                            {
                                data: 'remarks',
                                name: 'remarks'
                            },
                            {
                                data: 'actions',
                                name: 'actions',
                                className: 'text-end',
                                orderable: false,
                                searchable: false
                            },
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

                            if (title === 'SI' || title === 'Actions' || title === '' || title === 'ID')
                                return;

                            const isChecked = column.visible() ? 'checked' : '';
                            const switchHtml = `
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input col-toggle-input" type="checkbox" 
                                           id="at_col_${index}" data-column="${index}" ${isChecked}>
                                    <label class="form-check-label" for="at_col_${index}">${title}</label>
                                </div>`;
                            container.append(switchHtml);
                        });
                    }

                    // Inject the "Save for All" button into the modal footer (Admin only)
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
                                            table_identifier: 'agreements_table',
                                            settings: JSON.stringify(state)
                                        },
                                        success: function() {
                                            alert('Agreement layout saved for all users!');
                                            window.location.reload();
                                        },
                                        error: function() {
                                            alert('Error saving settings.');
                                        }
                                    });
                                });
                        }
                    }

                    // Admin: Handle Toggle Clicks
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
            $(document).ready(() => AgreementTable.init());
        })();
    </script>
@endsection
