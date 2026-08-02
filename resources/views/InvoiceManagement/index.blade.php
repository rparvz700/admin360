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
            <div>
                @can('create-invoice')
                    <a href="{{ route('invoices.bulk-generate') }}" class="btn btn-success btn-sm me-2">
                        <i class="fa fa-calendar-plus me-1"></i> Generate Monthly Rent Invoices
                    </a>
                @endcan
                <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> Add Invoice
                </a>
            </div>
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

<!-- Record Payment Modal (Index Page) -->
<div class="modal fade" id="modal-index-record-payment" tabindex="-1" aria-labelledby="modalIndexPaymentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-record-payment" action="" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title fs-sm text-white mb-0" id="modalIndexPaymentLabel">
                        <i class="fa fa-credit-card me-1"></i> Record Payment: <span id="payment-invoice-number"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 fs-sm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Outstanding Amount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">৳</span>
                            <input type="text" class="form-control bg-light fw-bold text-danger" id="payment-outstanding-display" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_paid_amount">Amount Paid <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" class="form-control" id="payment_paid_amount" name="paid_amount" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_date_input">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="payment_date_input" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_method_select">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="payment_method_select" name="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-body-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-check me-1"></i> Record Payment
                    </button>
                </div>
            </form>
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

            // Open Record Payment Modal directly from Actions dropdown
            $(document).on('click', '.btn-record-payment', function(e) {
                e.preventDefault();
                const invoiceId     = $(this).data('invoice-id');
                const invoiceNumber = $(this).data('invoice-number');
                const outstanding   = parseFloat($(this).data('outstanding')) || 0;

                $('#payment-invoice-number').text(invoiceNumber);
                $('#payment-outstanding-display').val(outstanding.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#payment_paid_amount').val(outstanding.toFixed(2)).attr('max', outstanding);

                const actionUrl = '{{ url("invoices") }}/' + invoiceId + '/pay';
                $('#form-record-payment').attr('action', actionUrl);

                const modalEl = document.getElementById('modal-index-record-payment');
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            });
        });
    </script>
@endsection