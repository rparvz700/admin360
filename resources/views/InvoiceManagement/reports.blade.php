@extends('Partials.app', ['activeMenu' => 'invoices'])

@section('title') Invoice Reports & Analytics @endsection

@section('content')
<div class="content">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start mb-3">
        <div class="flex-grow-1 mb-2 mb-md-0">
            <h1 class="h3 fw-bold mb-1">
                <i class="fa fa-file-alt text-primary me-2"></i> Invoice Reports & Analytics Center
            </h1>
            <h2 class="h6 fw-medium text-muted mb-0">
                Audit, reconcile, analyze vendor statements, and export invoice data.
            </h2>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('invoices.reports.export', request()->all()) }}" class="btn btn-sm btn-success">
                <i class="fa fa-file-excel me-1"></i> Export CSV Report
            </a>
            <a href="{{ route('invoices.dashboard') }}" class="btn btn-sm btn-alt-secondary ms-1">
                <i class="fa fa-chart-line me-1"></i> View Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="block block-rounded mb-4">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-filter me-2 text-muted"></i> Report Filters</h3>
        </div>
        <div class="block-content">
            <form action="{{ route('invoices.reports') }}" method="GET" autocomplete="off">
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="date_from">Date From</label>
                        <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="date_to">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="billing_month">Billing Month</label>
                        <input type="month" class="form-control form-control-sm" id="billing_month" name="billing_month" value="{{ request('billing_month') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="invoice_type">Invoice Type</label>
                        <select class="form-select form-select-sm" id="invoice_type" name="invoice_type">
                            <option value="all" {{ request('invoice_type') == 'all' ? 'selected' : '' }}>-- All Types --</option>
                            <option value="rent" {{ request('invoice_type') == 'rent' ? 'selected' : '' }}>Rent Requisition</option>
                            <option value="maintenance" {{ request('invoice_type') == 'maintenance' ? 'selected' : '' }}>Vehicle Maintenance</option>
                            <option value="general" {{ request('invoice_type') == 'general' ? 'selected' : '' }}>General Service</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="vendor_id">Vendor</label>
                        <select class="form-select form-select-sm" id="vendor_id" name="vendor_id">
                            <option value="all">-- All Vendors --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }} ({{ $vendor->vendor_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="payment_status">Status</label>
                        <select class="form-select form-select-sm" id="payment_status" name="payment_status">
                            <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>-- All Statuses --</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('invoices.reports') }}" class="btn btn-secondary btn-sm me-2">
                        <i class="fa fa-sync-alt me-1"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2 mb-md-0">
            <div class="p-3 bg-body-light border rounded">
                <div class="fs-xs fw-bold text-uppercase text-muted">Total Gross Billed</div>
                <div class="fs-4 fw-bold text-primary">৳ {{ number_format($summary->total_sum, 2) }}</div>
                <small class="text-muted">{{ $summary->total_count }} Invoices in result</small>
            </div>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <div class="p-3 bg-body-light border rounded">
                <div class="fs-xs fw-bold text-uppercase text-muted">Tax & Discounts</div>
                <div class="fs-5 fw-bold text-dark">
                    Tax: ৳ {{ number_format($summary->tax_sum, 2) }}
                </div>
                <small class="text-muted">Disc: ৳ {{ number_format($summary->discount_sum, 2) }}</small>
            </div>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <div class="p-3 bg-body-light border rounded">
                <div class="fs-xs fw-bold text-uppercase text-muted">Total Paid</div>
                <div class="fs-4 fw-bold text-success">৳ {{ number_format($summary->paid_sum, 2) }}</div>
                <small class="text-success">Collected amount</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-body-light border rounded">
                <div class="fs-xs fw-bold text-uppercase text-muted">Balance Due</div>
                <div class="fs-4 fw-bold text-danger">৳ {{ number_format($summary->outstanding_sum, 2) }}</div>
                <small class="text-danger">Unpaid receivables</small>
            </div>
        </div>
    </div>

    <!-- Reports Data Block -->
    <div class="block block-rounded">
        <ul class="nav nav-tabs nav-tabs-block" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-invoices-btn" data-bs-toggle="tab" data-bs-target="#tab-invoices" role="tab">
                    <i class="fa fa-list me-1"></i> Itemized Invoices List ({{ $invoices->total() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-aging-btn" data-bs-toggle="tab" data-bs-target="#tab-aging" role="tab">
                    <i class="fa fa-history me-1"></i> Vendor Aging & Payable Analysis ({{ $vendorAging->count() }})
                </button>
            </li>
        </ul>

        <div class="block-content tab-content fs-sm py-3">
            <!-- Tab 1: Invoices List -->
            <div class="tab-pane active" id="tab-invoices" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-vcenter">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Type</th>
                                <th>Invoice For / Item</th>
                                <th>Vendor</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $inv->invoice_type_badge }}">
                                        {{ $inv->invoice_type_label }}
                                    </span>
                                </td>
                                <td>{!! $inv->invoice_item_html !!}</td>
                                <td>
                                    {{ $inv->vendor->name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $inv->vendor->vendor_code ?? '' }}</small>
                                </td>
                                <td>{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $inv->due_date ? $inv->due_date->format('d M Y') : 'N/A' }}</td>
                                <td class="text-end">৳ {{ number_format($inv->subtotal, 2) }}</td>
                                <td class="text-end">৳ {{ number_format($inv->discount_amount, 2) }}</td>
                                <td class="text-end fw-bold">৳ {{ number_format($inv->total_amount, 2) }}</td>
                                <td class="text-end text-success">৳ {{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="text-end text-danger fw-bold">
                                    ৳ {{ number_format($inv->getOutstandingAmount(), 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $inv->getPaymentStatusBadge() }}">
                                        {{ $inv->getPaymentStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">No invoice records found for the selected filters.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} records</small>
                    {{ $invoices->links() }}
                </div>
            </div>

            <!-- Tab 2: Vendor Aging Analysis -->
            <div class="tab-pane" id="tab-aging" role="tabpanel">
                <div class="alert alert-info py-2 mb-3">
                    <i class="fa fa-info-circle me-1"></i> Aging buckets show unpaid receivable balances grouped by vendor and days overdue.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-vcenter">
                        <thead class="table-light">
                            <tr>
                                <th>Vendor Name</th>
                                <th>Vendor Code</th>
                                <th class="text-end text-primary">Current (<= 30 Days)</th>
                                <th class="text-end text-warning">31 - 60 Days</th>
                                <th class="text-end text-danger">61 - 90 Days</th>
                                <th class="text-end text-dark bg-danger-light">Over 90 Days</th>
                                <th class="text-end text-danger fw-bold">Total Overdue Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendorAging as $aging)
                            <tr>
                                <td class="fw-bold">{{ $aging->vendor->name ?? 'N/A' }}</td>
                                <td>{{ $aging->vendor->vendor_code ?? 'N/A' }}</td>
                                <td class="text-end">৳ {{ number_format($aging->current_amount, 2) }}</td>
                                <td class="text-end text-warning">৳ {{ number_format($aging->days_1_30 + $aging->days_31_60, 2) }}</td>
                                <td class="text-end text-danger">৳ {{ number_format($aging->days_61_90, 2) }}</td>
                                <td class="text-end text-danger fw-bold">৳ {{ number_format($aging->days_over_90, 2) }}</td>
                                <td class="text-end text-danger fw-bold fs-6">৳ {{ number_format($aging->total_outstanding, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No vendor aging receivables currently pending.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
