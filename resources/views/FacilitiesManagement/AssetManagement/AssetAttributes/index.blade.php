@extends('Partials.app', ['activeMenu' => 'asset-attributes'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Asset Attributes
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Asset Attributes</h3>
                <a href="{{ route('asset-attributes.create') }}" class="btn btn-sm btn-primary">Add Attribute</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="asset-attributes-table" class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Type</th>
                                <!-- <th>Options</th> -->
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
            $('#asset-attributes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('asset-attributes.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'category', name: 'category' },
                    { data: 'attribute_name', name: 'attribute_name' },
                    { data: 'attribute_type', name: 'attribute_type' },
                    // { data: 'options', name: 'options' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
