@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title') Electricity Bills Directory @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <style>
        .meter-type-nav .nav-link {
            font-weight: 600;
            color: #4b5563;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 0.75rem 1.25rem;
        }
        .meter-type-nav .nav-link.active {
            color: #2563eb;
            background-color: transparent;
            border-bottom-color: #2563eb;
        }
        .meter-type-nav .nav-link:hover:not(.active) {
            color: #111827;
            border-bottom-color: #d1d5db;
        }
    </style>
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

        <!-- Meter Type Nav Tabs -->
        <div class="bg-body-extra-light border-bottom">
            <ul class="nav meter-type-nav px-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link active meter-tab-btn" data-type="postpaid">
                        <i class="fa fa-file-invoice text-primary me-1"></i> Postpaid Bills
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link meter-tab-btn" data-type="prepaid">
                        <i class="fa fa-bolt text-warning me-1"></i> Prepaid Bills
                    </button>
                </li>
            </ul>
        </div>

        <div class="block-content fs-sm data-content">
            <!-- Filter Toolbar -->
            <div class="row mb-4 bg-body-light p-3 border rounded">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_billing_month">Filter by Month</label>
                    <input type="month" class="form-control form-control-sm" id="filter_billing_month">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="filter_project_name">Filter by Project</label>
                    <select class="form-select form-select-sm" id="filter_project_name">
                        <option value="all">-- All Projects --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->name }}">{{ $p->name }}</option>
                        @endforeach
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-secondary w-100" id="btn-reset-filters">
                        <i class="fa fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Bulk Print Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <button type="button" id="btn-print-selected" class="btn btn-sm btn-alt-primary" disabled>
                        <i class="fa fa-print me-1"></i> Print Selected (<span id="selected-count">0</span>)
                    </button>
                    <button type="button" id="btn-print-filtered" class="btn btn-sm btn-alt-secondary">
                        <i class="fa fa-print-all me-1"></i> Print All Filtered (<span id="active-tab-label">Postpaid</span>)
                    </button>
                </div>
            </div>

            <!-- Electricity Bills Table -->
            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="bills-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px; pointer-events: auto;">
                                <input type="checkbox" id="check-all" class="form-check-input">
                            </th>
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
            var activeBillType = 'postpaid';

            var table = $('#bills-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('electricity.bills.index') }}",
                    data: function(d) {
                        d.billing_month = $('#filter_billing_month').val();
                        d.project_name = $('#filter_project_name').val();
                        d.status = $('#filter_status').val();
                        d.bill_type = activeBillType;
                    }
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="bill-checkbox form-check-input" value="' + data + '" data-project="' + (row.project_name || '') + '" data-type="' + (row.bill_type || '') + '">';
                        }
                    },
                    { data: 'requisition_no', name: 'requisition_no', searchable: true },
                    { data: 'site', name: 'site', searchable: true },
                    { data: 'meter', name: 'meter', searchable: true },
                    { data: 'billing_month', name: 'billing_month', searchable: true },
                    { data: 'total_amount_formatted', name: 'total_amount', className: 'text-end', searchable: false },
                    { data: 'payment_mode', name: 'payment_mode', searchable: true },
                    { data: 'status', name: 'status', className: 'text-center', searchable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'desc']]
            });

            // Tab switching handler
            $('.meter-tab-btn').on('click', function(e) {
                e.preventDefault();
                $('.meter-tab-btn').removeClass('active');
                $(this).addClass('active');

                activeBillType = $(this).data('type');
                $('#active-tab-label').text(activeBillType.charAt(0).toUpperCase() + activeBillType.slice(1));

                // Clear selection state
                $('#check-all').prop('checked', false);
                $('.bill-checkbox').prop('checked', false);
                selectedProject = null;
                selectedType = null;
                updatePrintSelectedState();

                table.draw();
            });

            // Trigger table reload when filter inputs change
            $('#filter_billing_month, #filter_project_name, #filter_status').on('change input', function() {
                table.draw();
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function() {
                $('#filter_billing_month').val('');
                $('#filter_project_name').val('all');
                $('#filter_status').val('all');
                table.draw();
            });

            // State variables for validation
            var selectedProject = null;
            var selectedType = null;

            // Reset constraints when table draws
            table.on('draw', function() {
                $('#check-all').prop('checked', false);
                selectedProject = null;
                selectedType = null;
                updatePrintSelectedState();
            });

            // Check/Uncheck all checkboxes
            $('#check-all').on('change', function() {
                var checked = $(this).is(':checked');
                if (checked) {
                    var firstCheckbox = $('.bill-checkbox').first();
                    if (firstCheckbox.length > 0) {
                        var firstProject = firstCheckbox.attr('data-project');
                        var firstType = firstCheckbox.attr('data-type');
                        var valid = true;
                        
                        $('.bill-checkbox').each(function() {
                            if ($(this).attr('data-project') !== firstProject || $(this).attr('data-type') !== firstType) {
                                valid = false;
                                return false;
                            }
                        });
                        
                        if (!valid) {
                            $(this).prop('checked', false);
                            alert('Validation Error: The bills on the current page belong to different Projects. Please filter by Project first before checking all.');
                            return;
                        }
                        
                        selectedProject = firstProject;
                        selectedType = firstType;
                        $('.bill-checkbox').prop('checked', true);
                    }
                } else {
                    $('.bill-checkbox').prop('checked', false);
                    selectedProject = null;
                    selectedType = null;
                }
                updatePrintSelectedState();
            });

            // Individual checkbox change listener
            $(document).on('change', '.bill-checkbox', function() {
                var $checkbox = $(this);
                var checked = $checkbox.is(':checked');

                if (checked) {
                    var project = $checkbox.attr('data-project');
                    var type = $checkbox.attr('data-type');

                    if (selectedProject === null && selectedType === null) {
                        selectedProject = project;
                        selectedType = type;
                    } else {
                        if (selectedProject !== project || selectedType !== type) {
                            $checkbox.prop('checked', false);
                            alert('Validation Error: You can only select bills belonging to the same Project for printing.');
                            return;
                        }
                    }
                } else {
                    if ($('.bill-checkbox:checked').length === 0) {
                        selectedProject = null;
                        selectedType = null;
                    }
                }

                if (!checked) {
                    $('#check-all').prop('checked', false);
                } else {
                    if ($('.bill-checkbox:checked').length === $('.bill-checkbox').length) {
                        $('#check-all').prop('checked', true);
                    }
                }
                updatePrintSelectedState();
            });

            function getSelectedIds() {
                var ids = [];
                $('.bill-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });
                return ids;
            }

            function updatePrintSelectedState() {
                var count = getSelectedIds().length;
                $('#selected-count').text(count);
                $('#btn-print-selected').prop('disabled', count === 0);
            }

            // Print Selected handler
            $('#btn-print-selected').on('click', function() {
                var ids = getSelectedIds();
                if (ids.length > 0) {
                    var url = "{{ route('electricity.bills.bulk-print') }}?ids=" + ids.join(',');
                    window.open(url, '_blank');
                }
            });

            // Print All Filtered handler
            $('#btn-print-filtered').on('click', function() {
                var billingMonth = $('#filter_billing_month').val();
                var projectName = $('#filter_project_name').val();
                var status = $('#filter_status').val();
                
                var url = "{{ route('electricity.bills.bulk-print') }}?" + $.param({
                    billing_month: billingMonth,
                    project_name: projectName,
                    bill_type: activeBillType,
                    status: status
                });
                window.open(url, '_blank');
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
