@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Invoice Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Invoice - {{ $invoice->invoice_number }}</h3>
            <div>
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                @if($invoice->payment_status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm ms-1">Edit</a>
                @endif
                @if($invoice->invoice_file_path)
                    <a href="{{ asset('storage/' . $invoice->invoice_file_path) }}" target="_blank" class="btn btn-info btn-sm ms-1">
                        <i class="fa fa-download"></i> Download File
                    </a>
                @endif
            </div>
        </div>
        <div class="block-content">

            <!-- Status Banner -->
            @if($invoice->isOverdue())
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle me-1"></i>
                    This invoice is <strong>overdue</strong>. Due date was {{ $invoice->due_date->format('d M Y') }}.
                </div>
            @endif

            <div class="row">
                <!-- Invoice Details -->
                <div class="col-md-6">
                    <h5 class="mb-3 border-bottom pb-2">Invoice Details</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="40%">Invoice Number</th>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Vendor</th>
                                <td>
                                    {{ $invoice->vendor->name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $invoice->vendor->vendor_code ?? '' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Invoice Date</th>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th>Due Date</th>
                                <td>
                                    {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}
                                    @if($invoice->isOverdue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $invoice->remarks ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Details -->
                <div class="col-md-6">
                    <h5 class="mb-3 border-bottom pb-2">Payment Details</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="40%">Subtotal</th>
                                <td>৳ {{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Tax Amount</th>
                                <td>৳ {{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Discount Amount</th>
                                <td>৳ {{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                            <tr class="table-light">
                                <th>Total Amount</th>
                                <td><strong class="fs-5">৳ {{ number_format($invoice->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Paid Amount</th>
                                <td class="text-success">৳ {{ number_format($invoice->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Outstanding</th>
                                <td class="{{ $invoice->getOutstandingAmount() > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    ৳ {{ number_format($invoice->getOutstandingAmount(), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    <span class="badge bg-{{ $invoice->getPaymentStatusBadge() }} fs-6">
                                        {{ $invoice->getPaymentStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            @if($invoice->payment_date)
                            <tr>
                                <th>Payment Date</th>
                                <td>{{ $invoice->payment_date->format('d M Y') }}</td>
                            </tr>
                            @endif
                            @if($invoice->payment_method)
                            <tr>
                                <th>Payment Method</th>
                                <td>{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Record Payment Button -->
            @if($invoice->payment_status !== 'paid')
            <div class="mt-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fa fa-money-bill me-1"></i> Record Payment
                </button>
            </div>
            @endif

        </div>
    </div>

    <!-- Linked Maintenances -->
    @if($invoice->maintenances->count() > 0)
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title">Linked Maintenance Records</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->maintenances as $maintenance)
                        <tr>
                            <td>{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">
                                    {{ $maintenance->getMaintenanceTypeLabel() }}
                                </span>
                            </td>
                            <td>{{ $maintenance->start_datetime->format('d M Y') }}</td>
                            <td>৳ {{ number_format($maintenance->total_service_cost, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($maintenance->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Record Payment Modal -->
@if($invoice->payment_status !== 'paid')
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" class="form-control bg-light" value="৳ {{ number_format($invoice->getOutstandingAmount(), 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modal_paid_amount">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="modal_paid_amount" name="paid_amount"
                               value="{{ $invoice->getOutstandingAmount() }}" min="0.01"
                               max="{{ $invoice->getOutstandingAmount() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modal_payment_date">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="modal_payment_date" name="payment_date"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modal_payment_method">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection