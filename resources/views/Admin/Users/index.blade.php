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
                                {{-- <th class="">Phone</th>
                                <th class="all">HR ID</th>
                                <th class="all">Designation</th>
                                <th class="all">Department</th>
                                <th class="all">Subcenter</th> --}}
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
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins for datatable-->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>

    {{-- <script src="{{ asset('js/pages/be_tables_datatables.min.js') }}"></script> --}}

    {{-- <script>
        $('#ticket-datatable').DataTable({
            order: [
                [0, 'asc']
            ]
        });
    </script> --}}

    <script>
        !(function() {
            class e {
                static initDataTables() {
                    jQuery.extend(
                            jQuery.fn.DataTable.ext.classes, {
                                sWrapper: "dataTables_wrapper dt-bootstrap5",
                                sFilterInput: "form-control form-control-sm",
                                sLengthSelect: "form-select form-select-sm"
                            }
                        ),
                        jQuery.extend(!0, jQuery.fn.DataTable.defaults, {
                            language: {
                                lengthMenu: "_MENU_",
                                search: "_INPUT_",
                                searchPlaceholder: "Search..",
                                info: "Page <strong>_PAGE_</strong> of <strong>_PAGES_</strong>",
                                paginate: {
                                    first: '<i class="fa fa-angle-double-left"></i>',
                                    previous: '<i class="fa fa-angle-left"></i>',
                                    next: '<i class="fa fa-angle-right"></i>',
                                    last: '<i class="fa fa-angle-double-right"></i>'
                                },
                            },
                        }),
                        jQuery.extend(
                            !0,
                            jQuery.fn.DataTable.Buttons.defaults, {
                                dom: {
                                    button: {
                                        className: "btn btn-sm btn-primary"
                                    }
                                }
                            }
                        ),
                        jQuery(".js-dataTable-responsive").DataTable({
                            ajax: '{{ $listRoute }}',
                            processing: true,
                            serverSide: true,
                            pagingType: "full_numbers",
                            pageLength: 10,
                            lengthMenu: [
                                [5, 10, 15, 20],
                                [5, 10, 15, 20],
                            ],
                            order: [
                                [0, 'desc']
                            ],
                            autoWidth: !1,
                            responsive: !0,
                            // dom: "<'row'<'col-sm-12'<'text-center bg-body-light py-2 mb-2'B>>><'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                            // buttons: ["copy", "csv", "excel", "pdf", "print"],
                            columns: [{
                                    data: 'id'
                                },
                                {
                                    data: 'name'
                                },
                                {
                                    data: 'email'
                                },
                                
                                {
                                    data: 'roles',
                                    searchable: false
                                },
                                {
                                    data: 'status'
                                },
                                {
                                    data: 'actions',
                                    searchable: false
                                }
                            ],
                        });
                }
                static init() {
                    this.initDataTables();
                }
            }
            One.onLoad(() => e.init());
        })();


        $(document).on('click', '.delete-button', function() {
            var userId = $(this).data('user-id');
            if (confirm('Do you want to delete this user?')) {
                $('#deleteForm' + userId).submit();
            }
        });
    </script>
@endsection
