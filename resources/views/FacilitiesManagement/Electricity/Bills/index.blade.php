@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title')
    Electricity Bills
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-file-invoice-dollar text-primary me-2"></i> Electricity Bills</h3>
                <div class="block-options">
                    <a href="{{ route('electricity.bills.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus me-1"></i> New Electricity Bill
                    </a>
                </div>
            </div>
            <div class="block-content block-content-full">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filters Toolbar -->
                <div class="row mb-4 bg-body-light p-3 rounded mx-0 border">
                    {{-- <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="filter_rio">Filter by RIO Zone</label>
                    <select class="form-select select2" id="filter_rio" style="width: 100%;">
                        <option value="all">All RIO Zones</option>
                        @foreach ($rios as $rio)
                            <option value="{{ $rio->id }}">{{ $rio->name }} ({{ $rio->code }})</option>
                        @endforeach
                    </select>
                </div> --}}
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label fs-sm fw-semibold" for="filter_status">Filter by Payment Status</label>
                        <select class="form-select select2" id="filter_status" style="width: 100%;">
                            <option value="all">All Statuses</option>
                            <option value="generated">Pending Payment</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm fw-semibold" for="filter_bill_type">Bill Type</label>
                        <select class="form-select select2" id="filter_bill_type" style="width: 100%;">
                            <option value="all">All Types</option>
                            <option value="postpaid">Postpaid</option>
                            <option value="prepaid">Prepaid</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100"
                        id="bills-table">
                        <thead>
                            <tr>
                                <th class="text-nowrap">Bill Ref No</th>
                                <th class="text-nowrap">Site / POP</th>
                                {{-- <th class="text-nowrap">RIO Zone</th> --}}
                                <th class="text-nowrap">Meter</th>
                                <th class="text-nowrap">Month</th>
                                <th class="text-end text-nowrap">Amount</th>
                                <th class="text-nowrap">Payment Mode</th>
                                <th class="text-center text-nowrap">Status</th>
                                <th class="text-center text-nowrap">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Mark as Paid -->
    <div class="modal fade" id="modal-mark-paid" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="mark-paid-form" method="POST">
                    @csrf
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default bg-success text-white">
                            <h3 class="block-title text-white">Record Payment</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option text-white" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm py-3">
                            <div class="alert alert-info py-2 mb-3">
                                <strong id="pay-req-no"></strong> — Total: <strong id="pay-req-amount"></strong>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment_date">Payment Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment_reference">BEFTN Transaction Ref / Cheque No / bKash
                                    TrxID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="payment_reference" name="payment_reference"
                                    placeholder="e.g. BEFTN-98214 or CHQ-00129" required>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body-light">
                            <button type="button" class="btn btn-sm btn-alt-secondary me-1"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check me-1"></i> Mark
                                Paid</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            var table = $('#bills-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search electricity bills...",
                    lengthMenu: "_MENU_ per page",
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-file-invoice-dollar fa-2x mb-2 d-block opacity-50"></i>No electricity bills generated yet</div>'
                },
                ajax: {
                    url: "{{ route('electricity.bills.index') }}",
                    data: function(d) {
                        d.rio_id = $('#filter_rio').val();
                        d.status = $('#filter_status').val();
                        d.bill_type = $('#filter_bill_type').val();
                    }
                },
                columns: [{
                        data: 'requisition_no',
                        name: 'requisition_no'
                    },
                    {
                        data: 'site',
                        name: 'site'
                    },

                    {
                        data: 'meter',
                        name: 'meter'
                    },
                    {
                        data: 'billing_month',
                        name: 'billing_month'
                    },
                    {
                        data: 'total_amount_formatted',
                        name: 'total_amount',
                        className: 'fw-bold text-dark text-end'
                    },
                    {
                        data: 'payment_mode',
                        name: 'payment_mode'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center text-nowrap'
                    }
                ]
            });

            $('#filter_rio, #filter_status, #filter_bill_type').on('change', function() {
                table.draw();
            });

            // Mark as Paid Modal
            $(document).on('click', '.mark-paid-btn', function() {
                var billId = $(this).data('id');
                var reqNo = $(this).data('req');
                var amount = $(this).data('amount');

                $('#mark-paid-form').attr('action', '/facilities-management/electricity/bills/' + billId +
                    '/pay');
                $('#pay-req-no').text(reqNo);
                $('#pay-req-amount').text('৳ ' + amount);
                $('#modal-mark-paid').modal('show');
            });
        });
    </script>
@endsection
