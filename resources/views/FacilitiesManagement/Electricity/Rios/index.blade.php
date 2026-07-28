@extends('Partials.app', ['activeMenu' => 'electricity-rios'])

@section('title') RIO Management @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-map-marked-alt text-primary me-2"></i> Regional Infrastructure Offices (RIOs)</h3>
            <div class="block-options">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-rio">
                    <i class="fa fa-plus me-1"></i> Add RIO
                </button>
            </div>
        </div>
        <div class="block-content block-content-full">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="rios-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Code</th>
                            <th class="text-nowrap">RIO Name</th>
                            <th class="text-center text-nowrap">Sites Tagged</th>
                            <th class="text-center text-nowrap">Users Assigned</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-center text-nowrap">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add RIO -->
<div class="modal fade" id="modal-add-rio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('electricity.rios.store') }}" method="POST">
                @csrf
                <div class="block block-rounded block-transparent mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Add New RIO</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content fs-sm py-3">
                        <div class="mb-3">
                            <label class="form-label" for="code">RIO Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="e.g. RIO-01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name">RIO Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Dhaka North RIO" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-end bg-body-light">
                        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save RIO</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit RIO -->
<div class="modal fade" id="modal-edit-rio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="edit-rio-form" method="POST">
                @csrf
                @method('PUT')
                <div class="block block-rounded block-transparent mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Edit RIO</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content fs-sm py-3">
                        <div class="mb-3">
                            <label class="form-label" for="edit_code">RIO Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_name">RIO Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-end bg-body-light">
                        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Update RIO</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Assign Users -->
<div class="modal fade" id="modal-assign-users" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="assign-users-form" method="POST">
                @csrf
                <div class="block block-rounded block-transparent mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Assign Users to RIO</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content fs-sm py-3">
                        <div class="mb-3">
                            <label class="form-label" for="user_ids">Select Admin / SC Users</label>
                            <select class="form-select select2" id="user_ids" name="user_ids[]" multiple style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-end bg-body-light">
                        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Assignments</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#modal-assign-users'),
                width: '100%'
            });

            var table = $('#rios-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search RIOs...",
                    lengthMenu: "_MENU_ per page",
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-map-marked-alt fa-2x mb-2 d-block opacity-50"></i>No RIO offices registered</div>'
                },
                ajax: "{{ route('electricity.rios.index') }}",
                columns: [
                    { data: 'code', name: 'code', className: 'fw-bold text-primary' },
                    { data: 'name', name: 'name' },
                    { data: 'building_count', name: 'building_count', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'user_count', name: 'user_count', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'is_active', name: 'is_active', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // Edit button click
            $(document).on('click', '.edit-rio-btn', function() {
                var rio = $(this).data('rio');
                $('#edit-rio-form').attr('action', '/facilities-management/electricity/rios/' + rio.id);
                $('#edit_code').val(rio.code);
                $('#edit_name').val(rio.name);
                $('#edit_is_active').prop('checked', rio.is_active);
                $('#modal-edit-rio').modal('show');
            });

            // Tag User button click
            $(document).on('click', '.tag-user-btn', function() {
                var rioId = $(this).data('id');
                var assignedUserIds = $(this).data('users');
                $('#assign-users-form').attr('action', '/facilities-management/electricity/rios/' + rioId + '/assign-users');
                $('#user_ids').val(assignedUserIds).trigger('change');
                $('#modal-assign-users').modal('show');
            });
        });
    </script>
@endsection
