@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Assets
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <!-- Filter Cards -->
        <div class="row items-push">
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter active" href="javascript:void(0)"
                    data-category-id="all">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-dark">All Assets</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="1">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-primary">Air Conditioner</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="10">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-success">Fire Extinguisher</div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-pop text-center category-filter" href="javascript:void(0)"
                    data-category-id="26">
                    <div class="block-content block-content-full">
                        <div class="fs-lg fw-semibold text-warning">Aircon Timer</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Assets</h3>
                <a href="{{ route('assets.create') }}" class="btn btn-sm btn-primary">Add Asset</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="assets-table"
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover"
                        style="width:100%">
                        <thead>

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
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            let selectedCategoryId = 'all';
            let table = null;

            // Define Base Columns that always exist
            const baseColumns = [{
                    data: 'id',
                    name: 'id',
                    title: 'ID'
                },
                {
                    data: 'site_code',
                    name: 'site_code',
                    title: 'Site Code'
                },
                {
                    data: 'building_floor',
                    name: 'building_floor',
                    title: 'Site Name'
                },
                {
                    data: 'floor',
                    name: 'floor',
                    title: 'Floor'
                },
                {
                    data: 'subcenter',
                    name: 'subcenter',
                    title: 'Subcenter'
                },
                {
                    data: 'location_within_floor',
                    name: 'location_within_floor',
                    title: 'Location'
                },
                {
                    data: 'category',
                    name: 'category',
                    title: 'Category'
                },
                {
                    data: 'brand',
                    name: 'brand',
                    title: 'Brand'
                },
                {
                    data: 'model',
                    name: 'model',
                    title: 'Model'
                },
                {
                    data: 'serial_number',
                    name: 'serial_number',
                    title: 'Serial No'
                },
                {
                    data: 'purchase_date',
                    name: 'purchase_date',
                    title: 'Purchase Date'
                },
                {
                    data: 'warranty_expiry',
                    name: 'warranty_expiry',
                    title: 'Warranty'
                },
                {
                    data: 'parent',
                    name: 'parent',
                    title: 'Parent'
                },
                {
                    data: 'project',
                    name: 'project',
                    title: 'Project'
                },
                {
                    data: 'status',
                    name: 'status',
                    title: 'Status'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    title: 'Actions',
                    orderable: false,
                    searchable: false
                }
            ];

            function initDataTable(categoryId) {
                // If table exists, destroy it and empty the element
                if ($.fn.DataTable.isDataTable('#assets-table')) {
                    table.destroy();
                    $('#assets-table').empty();
                }

                table = $('#assets-table').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [
                        [0, 'desc']
                    ],
                    ajax: {
                        url: '{{ route('assets.index') }}',
                        data: {
                            category_id: categoryId
                        }
                    },
                    // DrawCallback allows us to detect the dynamic columns sent from the server
                    initComplete: function(settings, json) {
                        // This is a bit tricky: DataTables requires columns defined AT START.
                        // Because we don't know the attributes until the first AJAX call, 
                        // we handle the logic in the click event below.
                    },
                    columns: baseColumns // Default start
                });
            }

            // High-level function to handle category change
            function refreshTableWithDynamicColumns(categoryId) {
                // 1. First, fetch the meta-data (attributes) for this category
                // Or simply perform one AJAX call to get the columns
                $.ajax({
                    url: '{{ route('assets.index') }}',
                    data: {
                        category_id: categoryId,
                        draw: 1,
                        length: 1
                    }, // Small request to get structure
                    success: function(response) {
                        let columns = [...baseColumns];

                        // 2. Insert dynamic attributes before the 'actions' column
                        if (response.dynamic_attributes && response.dynamic_attributes.length > 0) {
                            console.log(response.dynamic_attributes);
                            response.dynamic_attributes.forEach(attr => {
                                columns.splice(columns.length - 1, 0, {
                                    data: 'attr_' + attr.id,
                                    name: 'attr_' + attr.id,
                                    title: attr
                                        .attribute_name, // Make sure this matches your column name
                                    searchable: false
                                });
                            });
                        }

                        // 3. Destroy and Rebuild the Table with new column definitions
                        if ($.fn.DataTable.isDataTable('#assets-table')) {
                            table.destroy();
                            $('#assets-table').empty();
                        }

                        table = $('#assets-table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route('assets.index') }}',
                                data: {
                                    category_id: categoryId
                                }
                            },
                            columns: columns,
                            responsive: false,
                            scrollX: true,
                            autoWidth: false,
                            fixedHeader: true

                        });
                    }
                });
            }

            // Initial Load
            refreshTableWithDynamicColumns('all');

            // Filter Click Event
            $('.category-filter').on('click', function() {
                $('.category-filter').removeClass('active');
                $(this).addClass('active');

                selectedCategoryId = $(this).data('category-id');
                refreshTableWithDynamicColumns(selectedCategoryId);
            });
        });
    </script>

    <style>
        /* Highlight the active card */
        .category-filter.active {
            border: 2px solid #0665d0 !important;
            box-shadow: 0 0 10px rgba(6, 101, 208, 0.2);
        }

        .category-filter {
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
    </style>
@endsection
