@extends('Partials.app', ['activeMenu' => 'invoices'])

@section('title') Rent Requisition Invoices @endsection

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
                <small class="mb-0"><i class="fa fa-check-circle me-1"></i> {{ Session::get('success') }}</small>
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
            <h3 class="block-title"><i class="fa fa-building me-2 text-primary"></i> Rent Requisition Invoices</h3>
            <div>
                @canany(['create-rent-invoice', 'create-invoice'])
                    <a href="{{ route('invoices.rent.bulk-generate') }}" class="btn btn-success btn-sm me-2">
                        <i class="fa fa-calendar-plus me-1"></i> Generate Monthly Rent Invoices
                    </a>
                @endcanany
            </div>
        </div>

        <div class="block-content fs-sm data-content">
            <!-- Filter Toolbar -->
            <div class="row mb-4 bg-body-light p-3 border rounded">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_billing_month">Filter by Billing Month</label>
                    <input type="month" class="form-control form-control-sm" id="filter_billing_month">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_payment_status">Filter by Payment Status</label>
                    <select class="form-select form-select-sm" id="filter_payment_status">
                        <option value="all">-- All Payment Statuses --</option>
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

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="rent-invoices-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Invoice #</th>
                            <th class="text-nowrap">Rent Details / Site</th>
                            <th class="text-nowrap">Vendor / Owner</th>
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

<!-- Record Payment Modal -->
<div class="modal fade" id="modal-record-payment" tabindex="-1" aria-labelledby="modalPaymentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-record-payment" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title fs-sm text-white mb-0" id="modalPaymentLabel">
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
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="cash">Cash</option>
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
    <script>
        $(function() {
            var table = $('#rent-invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('invoices.rent.index') }}",
                    data: function(d) {
                        d.billing_month = $('#filter_billing_month').val();
                        d.payment_status = $('#filter_payment_status').val();
                    }
                },
                columns: [
                    { data: 'invoice_number', name: 'invoice_number', searchable: true },
                    { data: 'item_details', name: 'item_details', searchable: true },
                    { data: 'vendor', name: 'vendor', searchable: true },
                    { data: 'total_amount', name: 'total_amount', className: 'text-end', searchable: false },
                    { data: 'paid_amount', name: 'paid_amount', className: 'text-end', searchable: false },
                    { data: 'outstanding', name: 'outstanding', className: 'text-end', searchable: false },
                    { data: 'payment_status', name: 'payment_status', className: 'text-center', searchable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']]
            });

            $('#filter_billing_month, #filter_payment_status').on('change input', function() {
                table.draw();
            });

            $('#btn-reset-filters').on('click', function() {
                $('#filter_billing_month').val('');
                $('#filter_payment_status').val('all');
                table.draw();
            });

            $(document).on('click', '.btn-record-payment', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var num = $(this).data('number');
                var outstanding = $(this).data('outstanding');

                $('#form-record-payment').attr('action', '{{ url("invoices/rent") }}/' + id + '/pay');
                $('#payment-invoice-number').text(num);
                $('#payment-outstanding-display').val('৳ ' + outstanding);

                const modalEl = document.getElementById('modal-record-payment');
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            });
        });
    </script>
@endsection
