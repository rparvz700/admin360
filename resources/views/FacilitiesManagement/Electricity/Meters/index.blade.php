@extends('Partials.app', ['activeMenu' => 'electricity-meters'])

@section('title') Meters Master @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-tachometer-alt text-warning me-2"></i> Electricity Meters Master</h3>
            <div class="block-options">
                <a href="{{ route('electricity.meters.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Register New Meter
                </a>
            </div>
        </div>
        <div class="block-content block-content-full">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters Toolbar -->
            <div class="row mb-4 bg-body-light p-3 rounded mx-0 border">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_meter_type">Filter by Meter Type</label>
                    <select class="form-select select2" id="filter_meter_type" style="width: 100%;">
                        <option value="all">All Meter Types</option>
                        <option value="postpaid_main">Postpaid Main Meter</option>
                        <option value="postpaid_sub">Postpaid Sub Meter</option>
                        <option value="prepaid">Prepaid Meter</option>
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
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_noc_status">Filter by NOC Status (6-Mo)</label>
                    <select class="form-select select2" id="filter_noc_status" style="width: 100%;">
                        <option value="all">All NOC Statuses</option>
                        <option value="valid">NOC Valid</option>
                        <option value="expiring">NOC Expiring Soon (30 Days)</option>
                        <option value="missing">NOC Missing / Expired</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-alt-secondary w-100" id="reset_filters_btn">
                        <i class="fa fa-redo me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full w-100" id="meters-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Meter No</th>
                            <th class="text-nowrap">Consumer No</th>
                            <th class="text-nowrap">Site / POP</th>
                            <th class="text-nowrap">Floors</th>
                            <th class="text-nowrap">Meter Type</th>
                            <th class="text-nowrap">Due Date</th>
                            <th class="text-nowrap text-center">NOC Status (6-Mo)</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-center text-nowrap">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@include('FacilitiesManagement.Electricity.Meters.partials.noc_modal')
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

            var metersTable = $('#meters-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: false,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search meters...",
                    lengthMenu: "_MENU_ per page",
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-tachometer-alt fa-2x mb-2 d-block opacity-50"></i>No meters registered yet</div>'
                },
                ajax: {
                    url: "{{ route('electricity.meters.index') }}",
                    data: function(d) {
                        d.meter_type = $('#filter_meter_type').val();
                        d.is_active = $('#filter_status').val();
                        d.noc_status = $('#filter_noc_status').val();
                    }
                },
                columns: [
                    { data: 'meter_number', name: 'meter_number' },
                    { data: 'consumer_no', name: 'consumer_no', defaultContent: 'N/A' },
                    { data: 'site', name: 'site' },
                    { data: 'floors_list', name: 'floors_list', defaultContent: 'N/A' },
                    { data: 'meter_type_badge', name: 'meter_type_badge' },
                    { data: 'due_date_day', name: 'due_date_day', defaultContent: 'N/A' },
                    { data: 'noc_status', name: 'noc_status', className: 'text-center' },
                    { data: 'is_active', name: 'is_active', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center text-nowrap' }
                ]
            });

            $('#filter_meter_type, #filter_status, #filter_noc_status').on('change', function() {
                metersTable.draw();
            });

            $('#reset_filters_btn').on('click', function() {
                $('#filter_meter_type').val('all').trigger('change.select2');
                $('#filter_status').val('all').trigger('change.select2');
                $('#filter_noc_status').val('all').trigger('change.select2');
                metersTable.draw();
            });

            // NOC Modal Open & Data Fetch
            $(document).on('click', '.open-noc-modal', function() {
                var meterId = $(this).data('id');
                var meterNumber = $(this).data('number');
                $('#noc_meter_id').val(meterId);
                $('#noc_meter_number_title').text(meterNumber);
                loadNocHistory(meterId);
                $('#nocManagementModal').modal('show');
            });

            function loadNocHistory(meterId) {
                $('#noc-history-tbody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-2"></i> Loading NOC documents...</td></tr>');
                $.ajax({
                    url: "{{ url('facilities-management/electricity/meters') }}/" + meterId + "/nocs",
                    method: 'GET',
                    success: function(res) {
                        if (res.has_active && res.active_noc) {
                            $('#noc_active_text').text('Active NOC Verified: ' + res.active_noc.noc_number);
                            $('#noc_active_period').text('Validity Period: ' + res.active_noc.period_formatted);
                            $('#noc_active_banner').slideDown();
                        } else {
                            $('#noc_active_banner').slideUp();
                        }

                        var html = '';
                        if (res.nocs && res.nocs.length > 0) {
                            res.nocs.forEach(function(noc) {
                                html += '<tr>';
                                html += '<td class="fw-semibold text-primary">' + escapeHtml(noc.noc_number) + '</td>';
                                html += '<td>' + escapeHtml(noc.period_formatted) + '</td>';
                                html += '<td>' + escapeHtml(noc.issuing_authority) + '</td>';
                                html += '<td><span class="badge bg-' + noc.status_badge + '">' + escapeHtml(noc.status_label) + '</span></td>';
                                html += '<td class="text-center"><a href="' + noc.file_url + '" target="_blank" class="btn btn-sm btn-alt-info"><i class="fa fa-file-pdf me-1"></i> View File</a></td>';
                                html += '<td class="text-center"><button type="button" class="btn btn-sm btn-alt-danger delete-noc-btn" data-id="' + noc.id + '"><i class="fa fa-trash"></i></button></td>';
                                html += '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="6" class="text-center py-4 text-muted">No 6-Month NOC documents uploaded yet for this meter.</td></tr>';
                        }
                        $('#noc-history-tbody').html(html);
                    }
                });
            }

            // NOC Upload Form Submit
            $('#nocUploadForm').on('submit', function(e) {
                e.preventDefault();
                var meterId = $('#noc_meter_id').val();
                var formData = new FormData(this);
                $('#saveNocBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...');

                $.ajax({
                    url: "{{ url('facilities-management/electricity/meters') }}/" + meterId + "/nocs",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        $('#saveNocBtn').prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload & Save NOC');
                        $('#nocUploadForm')[0].reset();
                        $('#noc-list-tab').tab('show');
                        loadNocHistory(meterId);
                        metersTable.draw();
                    },
                    error: function(err) {
                        $('#saveNocBtn').prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload & Save NOC');
                        alert('Error uploading NOC document. Please verify mandatory fields and file type.');
                    }
                });
            });

            // Delete NOC
            $(document).on('click', '.delete-noc-btn', function() {
                if (!confirm('Are you sure you want to delete this NOC record?')) return;
                var nocId = $(this).data('id');
                var meterId = $('#noc_meter_id').val();

                $.ajax({
                    url: "{{ url('facilities-management/electricity/meters/nocs') }}/" + nocId,
                    method: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function() {
                        loadNocHistory(meterId);
                        metersTable.draw();
                    }
                });
            });

            function escapeHtml(text) {
                return $('<div>').text(text || '').html();
            }
        });
    </script>
@endsection
