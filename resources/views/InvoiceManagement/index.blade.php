@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Invoices Directory @endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <small class="mb-0">{{ Session::get('success') }}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <small class="mb-0">{{ Session::get('error') }}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-file-invoice me-2 text-primary"></i> Invoices Directory</h3>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm float-end">
                <i class="fa fa-plus me-1"></i> Add Invoice
            </a>
        </div>

        <div class="block-content fs-sm data-content">
            <!-- Filter Toolbar -->
            <div class="row mb-4 bg-body-light p-3 border rounded">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_invoice_type">Filter by Invoice Type</label>
                    <select class="form-select form-select-sm" id="filter_invoice_type">
                        <option value="">-- All Invoice Types --</option>
                        <option value="rent">Rent Requisition</option>
                        <option value="maintenance">Vehicle Maintenance</option>
                        <option value="general">General Service</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_payment_status">Filter by Payment Status</label>
                    <select class="form-select form-select-sm" id="filter_payment_status">
                        <option value="">-- All Payment Statuses --</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-secondary w-100" id="btn-reset-filters">
                        <i class="fa fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="invoices-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Invoice #</th>
                            <th class="text-nowrap">Invoice Type</th>
                            <th class="text-nowrap">Vendor</th>
                            <th class="text-nowrap">Invoice Date</th>
                            <th class="text-nowrap">Due Date</th>
                            <th class="text-nowrap text-end">Total Amount</th>
                            <th class="text-nowrap text-end">Paid Amount</th>
                            <th class="text-nowrap text-end">Outstanding</th>
                            <th class="text-nowrap text-center">Status</th>
                            <th class="text-nowrap text-center">Actions</th>
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
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script>
        $(function() {
            var table = $('#invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('invoices.index') }}',
                    data: function(d) {
                        d.invoice_type = $('#filter_invoice_type').val();
                        d.payment_status = $('#filter_payment_status').val();
                    }
                },
                columns: [
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'invoice_type', name: 'invoice_type', orderable: false, searchable: false },
                    { data: 'vendor', name: 'vendor' },
                    { data: 'invoice_date', name: 'invoice_date' },
                    { data: 'due_date', name: 'due_date' },
                    { data: 'total_amount', name: 'total_amount', className: 'text-end' },
                    { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
                    { data: 'outstanding', name: 'outstanding', className: 'text-end' },
                    { data: 'payment_status', name: 'payment_status', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                order: [[3, 'desc']]
            });

            // Trigger table reload when filter dropdowns change
            $('#filter_invoice_type, #filter_payment_status').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function() {
                $('#filter_invoice_type').val('');
                $('#filter_payment_status').val('');
                table.draw();
            });
        });
    </script>
@endsection