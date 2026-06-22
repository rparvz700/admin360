@extends('Partials.app', ['activeMenu' => 'utility-types'])

@section('title')
    {{ config('app.name') }} - Utility Types
@endsection

@section('page_title')
    Utility Types
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2 text-center text-sm-start">
            <div class="flex-grow-1 mb-1 mb-sm-0">
                <div class="text-muted fs-sm">Facilities Management</div>
                <h1 class="h3 fw-bold mb-1">Utility Types</h1>
                <p class="text-muted mb-0">Manage custom utility and service charge categories (e.g., Guard Bill, Maid Bill).</p>
            </div>
            <a href="{{ route('utility-types.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Add Utility Type
            </a>
        </div>

        <div class="block block-rounded block-bordered mt-4">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible m-3 mb-0" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="block-header block-header-default">
                <h3 class="block-title">Utility Type Directory</h3>
            </div>
            <div class="block-content fs-sm py-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">SI</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th style="width: 150px;">Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.js-dataTable-responsive').DataTable({
                ajax: '{{ route('utility-types.index') }}',
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                pagingType: "full_numbers",
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'SI',
                        className: 'text-center text-muted',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'fw-semibold'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endsection
