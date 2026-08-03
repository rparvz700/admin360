@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title') Electricity Bills Directory @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <small class="mb-0"><i class="fa fa-check-circle me-1"></i> {{ session('success') }}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <small class="mb-0">{{ session('error') }}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-file-invoice-dollar me-2 text-primary"></i> Electricity Bills Directory</h3>
            <div>
                <a href="{{ route('electricity.bills.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> New Electricity Bill
                </a>
            </div>
        </div>

        <div class="block-content fs-sm data-content">
            <!-- Filter Toolbar -->
            <div class="row mb-4 bg-body-light p-3 border rounded">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_billing_month">Filter by Month</label>
                    <input type="month" class="form-control form-control-sm" id="filter_billing_month">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_bill_type">Filter by Bill Type</label>
                    <select class="form-select form-select-sm" id="filter_bill_type">
                        <option value="all">-- All Bill Types --</option>
                        <option value="postpaid">Postpaid</option>
                        <option value="prepaid">Prepaid</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_status">Filter by Payment Status</label>
                    <select class="form-select form-select-sm" id="filter_status">
                        <option value="all">-- All Payment Statuses --</option>
                        <option value="generated">Pending Payment</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-secondary w-100" id="btn-reset-filters">
                        <i class="fa fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Electricity Bills Table -->
            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="bills-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Bill Ref No</th>
                            <th class="text-nowrap">Site / POP</th>
                            <th class="text-nowrap">Meter</th>
                            <th class="text-nowrap">Month</th>
                            <th class="text-nowrap text-end">Total Amount</th>
                            <th class="text-nowrap">Payment Mode</th>
                            <th class="text-nowrap text-center">Status</th>
                            <th class="text-nowrap text-center">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="modal-mark-paid" tabindex="-1" aria-labelledby="modalMarkPaidLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="mark-paid-form" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title fs-sm text-white mb-0" id="modalMarkPaidLabel">
                        <i class="fa fa-credit-card me-1"></i> Record Payment: <span id="pay-req-no"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 fs-sm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Amount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">৳</span>
                            <input type="text" class="form-control bg-light fw-bold text-success" id="pay-req-amount-input" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_date">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_reference">BEFTN Ref / Cheque No / bKash TrxID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="payment_reference" name="payment_reference" placeholder="e.g. BEFTN-98214 or CHQ-00129" required>
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
            var table = $('#bills-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('electricity.bills.index') }}",
                    data: function(d) {
                        d.billing_month = $('#filter_billing_month').val();
                        d.status = $('#filter_status').val();
                        d.bill_type = $('#filter_bill_type').val();
                    }
                },
                columns: [
                    { data: 'requisition_no', name: 'requisition_no', searchable: true },
                    { data: 'site', name: 'site', searchable: true },
                    { data: 'meter', name: 'meter', searchable: true },
                    { data: 'billing_month', name: 'billing_month', searchable: true },
                    { data: 'total_amount_formatted', name: 'total_amount', className: 'text-end', searchable: false },
                    { data: 'payment_mode', name: 'payment_mode', searchable: true },
                    { data: 'status', name: 'status', className: 'text-center', searchable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']]
            });

            // Trigger table reload when filter inputs change
            $('#filter_billing_month, #filter_status, #filter_bill_type').on('change input', function() {
                table.draw();
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function() {
                $('#filter_billing_month').val('');
                $('#filter_status').val('all');
                $('#filter_bill_type').val('all');
                table.draw();
            });

            // Open Mark as Paid Modal
            $(document).on('click', '.mark-paid-btn', function(e) {
                e.preventDefault();
                var billId = $(this).data('id');
                var reqNo  = $(this).data('req');
                var amount = $(this).data('amount');

                $('#mark-paid-form').attr('action', '{{ url("facilities-management/electricity/bills") }}/' + billId + '/pay');
                $('#pay-req-no').text(reqNo);
                $('#pay-req-amount-input').val('৳ ' + amount);

                const modalEl = document.getElementById('modal-mark-paid');
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            });
        });
    </script>
@endsection
