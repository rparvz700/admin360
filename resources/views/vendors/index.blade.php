@extends('Partials.app', ['activeMenu' => 'vendors'])

@section('title') Vendor Master Directory @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-store text-primary me-2"></i> Master Vendor Directory</h3>
            <div class="block-options">
                @can('create-vendor')
                    <a href="{{ route('vendors.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus me-1"></i> Add Vendor
                    </a>
                @endcan
            </div>
        </div>
        <div class="block-content block-content-full">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters Toolbar -->
            <div class="row mb-4 bg-body-light p-3 rounded mx-0 border">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_module_scope">Filter by Module Scope</label>
                    <select class="form-select select2" id="filter_module_scope" style="width: 100%;">
                        <option value="all">All Modules</option>
                        <option value="facilities">Facilities (Landlords/Agreements)</option>
                        <option value="vehicle">Vehicle (Workshops/Parts)</option>
                        <option value="utility">Electricity &amp; Utilities</option>
                        <option value="general">General Procurement</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_category">Filter by Category</label>
                    <select class="form-select select2" id="filter_category" style="width: 100%;">
                        <option value="all">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_status">Filter by Status</label>
                    <select class="form-select select2" id="filter_status" style="width: 100%;">
                        <option value="all">All Statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-sm fw-semibold" for="filter_rating">Minimum Rating</label>
                    <select class="form-select select2" id="filter_rating" style="width: 100%;">
                        <option value="all">Any Rating</option>
                        <option value="4">4 Stars &amp; Above</option>
                        <option value="3">3 Stars &amp; Above</option>
                        <option value="2">2 Stars &amp; Above</option>
                        <option value="0">Unrated Only</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full w-100" id="vendors-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Code</th>
                            <th class="text-nowrap">Vendor Name</th>
                            <th class="text-nowrap">Categories</th>
                            <th class="text-nowrap">Contact Info</th>
                            <th class="text-nowrap">Bank / Tax Info</th>
                            <th class="text-nowrap">Rating</th>
                            <th class="text-center text-nowrap">Jobs</th>
                            <th class="text-end text-nowrap">Total Cost</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-center text-nowrap">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function toNumber(value) {
                var n = parseFloat(String(value == null ? '' : value).replace(/,/g, ''));
                return isNaN(n) ? 0 : n;
            }

            var vendorsTable = $('#vendors-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
                responsive: false,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search vendors...",
                    lengthMenu: "_MENU_ per page",
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-store fa-2x mb-2 d-block opacity-50"></i>No vendors registered yet</div>',
                    zeroRecords: '<div class="text-center py-4 text-muted"><i class="fa fa-search fa-2x mb-2 d-block opacity-50"></i>No vendors match the current filters</div>'
                },
                ajax: {
                    url: "{{ route('vendors.index') }}",
                    dataSrc: 'data'
                },
                columns: [
                    {
                        data: 'vendor_code',
                        className: 'text-nowrap',
                        render: function(data, type) {
                            if (type !== 'display') { return data || ''; }
                            return '<span class="fw-semibold font-monospace fs-sm">' + escapeHtml(data || '—') + '</span>';
                        }
                    },
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            if (type !== 'display') { return (data || '') + ' ' + (row.email || ''); }
                            var html = '<div class="fw-semibold text-primary">' + escapeHtml(data) + '</div>';
                            if (row.email) {
                                html += '<div class="fs-xs text-muted"><i class="fa fa-envelope me-1"></i>' + escapeHtml(row.email) + '</div>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'category_badges',
                        render: function(data, type, row) {
                            if (type !== 'display') { return row.vendor_type || ''; }
                            return data || '<span class="text-muted fs-xs">Uncategorized</span>';
                        }
                    },
                    {
                        data: 'contact_person',
                        render: function(data, type, row) {
                            if (type !== 'display') { return (data || '') + ' ' + (row.phone || ''); }
                            var html = data
                                ? '<div class="fw-medium fs-sm">' + escapeHtml(data) + '</div>'
                                : '<div class="text-muted fs-xs">No contact person</div>';
                            if (row.phone) {
                                html += '<div class="fs-xs text-muted"><i class="fa fa-phone me-1"></i>' + escapeHtml(row.phone) + '</div>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'bank_account_no',
                        render: function(data, type, row) {
                            if (type !== 'display') { return (data || '') + ' ' + (row.tin_vat_no || ''); }
                            var html = '<div class="fs-xs"><i class="fa fa-building-columns text-muted me-1"></i>' + escapeHtml(data) + '</div>';
                            if (row.tin_vat_no && row.tin_vat_no !== 'N/A') {
                                html += '<div class="fs-xs text-muted"><i class="fa fa-file-invoice me-1"></i>TIN: ' + escapeHtml(row.tin_vat_no) + '</div>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'rating',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            var value = toNumber(row.rating_raw);
                            if (type === 'sort' || type === 'type') { return value; }
                            if (type !== 'display') { return data || ''; }
                            if (!row.rating_raw && (!data || data === 'N/A')) {
                                return '<span class="text-muted fs-xs">Unrated</span>';
                            }
                            var stars = '';
                            for (var i = 1; i <= 5; i++) {
                                stars += '<i class="fa fa-star fs-xs ' + (i <= Math.round(value) ? 'text-warning' : 'text-muted opacity-50') + '"></i>';
                            }
                            return stars + ' <span class="fs-xs text-muted ms-1">' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        data: 'maintenances_count',
                        className: 'text-center',
                        render: function(data, type) {
                            var count = toNumber(data);
                            if (type === 'sort' || type === 'type') { return count; }
                            if (type !== 'display') { return String(data); }
                            var cls = count > 0 ? 'bg-primary-light text-primary' : 'bg-body-light text-muted';
                            return '<span class="badge ' + cls + '">' + count + '</span>';
                        }
                    },
                    {
                        data: 'total_cost',
                        className: 'text-end text-nowrap',
                        render: function(data, type) {
                            if (type === 'sort' || type === 'type') { return toNumber(data); }
                            if (type !== 'display') { return String(data); }
                            return '<span class="fw-semibold">৳ ' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        data: 'is_active',
                        className: 'text-center',
                        render: function(data, type) {
                            if (type !== 'display') { return data ? 'Active' : 'Inactive'; }
                            return data
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Inactive</span>';
                        }
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center text-nowrap'
                    }
                ]
            });

            // Client-side filtering
            $.fn.dataTable.ext.search.push(function(settings, searchData, dataIndex, rowData) {
                if (settings.nTable.id !== 'vendors-table') { return true; }

                var wantedStatus = $('#filter_status').val();
                var wantedRating = $('#filter_rating').val();

                if (wantedStatus && wantedStatus !== 'all') {
                    var isActive = rowData.is_active ? '1' : '0';
                    if (isActive !== wantedStatus) { return false; }
                }

                if (wantedRating && wantedRating !== 'all') {
                    var rating = toNumber(rowData.rating_raw);
                    if (wantedRating === '0') {
                        if (rowData.rating_raw) { return false; }
                    } else if (rating < parseFloat(wantedRating)) {
                        return false;
                    }
                }

                return true;
            });

            $('#filter_module_scope, #filter_category').on('change', function() {
                var moduleScope = $('#filter_module_scope').val();
                var categoryId = $('#filter_category').val();
                var params = {};

                if (moduleScope && moduleScope !== 'all') { params.module_scope = moduleScope; }
                if (categoryId && categoryId !== 'all') { params.category_id = categoryId; }

                var newUrl = "{{ route('vendors.index') }}" + ($.param(params) ? '?' + $.param(params) : '');
                vendorsTable.ajax.url(newUrl).load();
            });

            $('#filter_status, #filter_rating').on('change', function() {
                vendorsTable.draw();
            });

            $(document).on('click', '.js-vendor-delete', function(e) {
                e.preventDefault();
                var vendorId = $(this).data('vendor-id');
                if (confirm('Do you want to delete this vendor? This cannot be undone.')) {
                    $('#vendorDeleteForm' + vendorId).submit();
                }
            });
        });
    </script>
@endsection
