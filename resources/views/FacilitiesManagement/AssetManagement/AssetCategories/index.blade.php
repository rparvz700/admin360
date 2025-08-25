@extends('Partials.app', ['activeMenu' => 'asset-categories'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Asset Categories
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
                <h3 class="block-title">Asset Categories</h3>
                <a href="{{ route('asset-categories.create') }}" class="btn btn-sm btn-primary">Add Category</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="asset-categories-table" class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script>
        $(function () {
            $('#asset-categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('asset-categories.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'description', name: 'description' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
