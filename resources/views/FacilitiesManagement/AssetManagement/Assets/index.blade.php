@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Assets
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Assets</h3>
                <a href="{{ route('assets.create') }}" class="btn btn-sm btn-primary">Add Asset</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="assets-table" class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tag</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Serial No</th>
                                <th>Purchase Date</th>
                                <th>Warranty Expiry</th>
                                <th>Floor</th>
                                <th>Location</th>
                                <th>Parent</th>
                                <th>Status</th>
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
            $('#assets-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('assets.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'asset_tag', name: 'asset_tag' },
                    { data: 'asset_name', name: 'asset_name' },
                    { data: 'category', name: 'category' },
                    { data: 'brand', name: 'brand' },
                    { data: 'model', name: 'model' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'purchase_date', name: 'purchase_date' },
                    { data: 'warranty_expiry', name: 'warranty_expiry' },
                    { data: 'floor', name: 'floor' },
                    { data: 'location_within_floor', name: 'location_within_floor' },
                    { data: 'parent', name: 'parent' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
