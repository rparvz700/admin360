@extends('Partials.app', ['activeMenu' => 'invoices'])

@section('title') Rent Invoice Details - {{ $invoice->invoice_number }} @endsection

@section('content')
<div class="content">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start mb-3">
        <div class="flex-grow-1 mb-2 mb-md-0">
            <h1 class="h3 fw-bold mb-1">
                <i class="fa fa-building text-primary me-2"></i> Rent Invoice: {{ $invoice->invoice_number }}
            </h1>
            <h2 class="h6 fw-medium text-muted mb-0">
                Rent requisition breakdown and payment audit history.
            </h2>
        </div>
        <div>
            <a href="{{ route('invoices.rent.index') }}" class="btn btn-sm btn-secondary me-1">
                <i class="fa fa-arrow-left me-1"></i> Back to Rent Invoices
            </a>
            <a href="{{ route('invoices.rent.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-alt-secondary me-1">
                <i class="fa fa-print me-1"></i> Print Invoice
            </a>
            @if($invoice->payment_status !== 'paid')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal-record-payment">
                    <i class="fa fa-credit-card me-1"></i> Record Payment
                </button>
            @endif
        </div>
    </div>

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <small class="mb-0"><i class="fa fa-check-circle me-1"></i> {{ Session::get('success') }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Invoice & Agreement Summary Card -->
        <div class="col-md-8 mb-4">
            <div class="block block-rounded h-100">
                <div class="block-header block-header-default">
                    <h3 class="block-title"><i class="fa fa-info-circle me-2 text-primary"></i> Rent Invoice Information</h3>
                    <span class="badge bg-{{ $invoice->getPaymentStatusBadge() }} fs-xs">
                        {{ strtoupper($invoice->getPaymentStatusLabel()) }}
                    </span>
                </div>
                <div class="block-content fs-sm">
                    <table class="table table-striped table-borderless">
                        <tbody>
                            <tr>
                                <th class="w-30">Invoice Number</th>
                                <td class="fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Type</th>
                                <td><span class="badge bg-info fw-semibold">Rent Requisition</span></td>
                            </tr>
                            <tr>
                                <th>Vendor / Landlord</th>
                                <td class="fw-semibold">
                                    {{ $invoice->vendor->name ?? 'N/A' }}
                                    @if(!empty($invoice->vendor->vendor_code))
                                        <small class="text-muted">({{ $invoice->vendor->vendor_code }})</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Rent Item / Premises</th>
                                <td>{!! $invoice->invoice_item_html !!}</td>
                            </tr>
                            <tr>
                                <th>Billing Month</th>
                                <td class="fw-bold">{{ $invoice->billing_month ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Date</th>
                                <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Due Date</th>
                                <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Remarks / Notes</th>
                                <td class="text-muted">{{ $invoice->remarks ?: 'None' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Summary Card -->
        <div class="col-md-4 mb-4">
            <div class="block block-rounded h-100 border-start border-4 border-primary">
                <div class="block-header block-header-default">
                    <h3 class="block-title"><i class="fa fa-calculator me-2 text-primary"></i> Financial Summary</h3>
                </div>
                <div class="block-content fs-sm">
                    <div class="p-3 bg-body-light border rounded mb-3">
                        <div class="fs-xs fw-bold text-uppercase text-muted">Total Rent Amount</div>
                        <div class="fs-3 fw-bold text-dark">৳ {{ number_format($invoice->total_amount, 2) }}</div>
                    </div>
                    <div class="p-3 bg-success-light border border-success rounded mb-3">
                        <div class="fs-xs fw-bold text-uppercase text-success">Total Amount Paid</div>
                        <div class="fs-4 fw-bold text-success">৳ {{ number_format($invoice->paid_amount, 2) }}</div>
                        @if($invoice->payment_date)
                            <small class="text-muted">Last paid on {{ \Carbon\Carbon::parse($invoice->payment_date)->format('d M Y') }}</small>
                        @endif
                    </div>
                    <div class="p-3 bg-danger-light border border-danger rounded">
                        <div class="fs-xs fw-bold text-uppercase text-danger">Outstanding Balance</div>
                        <div class="fs-4 fw-bold text-danger">৳ {{ number_format($invoice->getOutstandingAmount(), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="modal-record-payment" tabindex="-1" aria-labelledby="modalPaymentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.rent.pay', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title fs-sm text-white mb-0" id="modalPaymentLabel">
                        <i class="fa fa-credit-card me-1"></i> Record Payment: {{ $invoice->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 fs-sm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Outstanding Amount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">৳</span>
                            <input type="text" class="form-control bg-light fw-bold text-danger" value="৳ {{ number_format($invoice->getOutstandingAmount(), 2) }}" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="paid_amount">Amount Paid <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" max="{{ $invoice->getOutstandingAmount() }}" min="0.01" value="{{ $invoice->getOutstandingAmount() }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_date">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="payment_method">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="payment_method" name="payment_method" required>
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
