@extends('Partials.app', ['activeMenu' => 'vendors'])

@section('title') Vendor Details @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-store text-primary me-2"></i> {{ $vendor->name }} ({{ $vendor->vendor_code }})</h3>
            <div class="block-options">
                <a href="{{ route('vendors.index') }}" class="btn btn-alt-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
                @can('edit-vendor')
                    <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-pencil-alt me-1"></i> Edit Vendor
                    </a>
                @endcan
            </div>
        </div>
        <div class="block-content">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr><th width="35%">Vendor Code</th><td><span class="fw-bold font-monospace">{{ $vendor->vendor_code }}</span></td></tr>
                            <tr><th>Name</th><td>{{ $vendor->name }}</td></tr>
                            <tr>
                                <th>Assigned Categories</th>
                                <td>{!! $vendor->getCategoryBadgesHtml() !!}</td>
                            </tr>
                            <tr><th>Contact Person</th><td>{{ $vendor->contact_person ?? 'N/A' }}</td></tr>
                            <tr><th>Phone</th><td>{{ $vendor->phone }}</td></tr>
                            <tr><th>Email</th><td>{{ $vendor->email ?? 'N/A' }}</td></tr>
                            <tr><th>Address</th><td>{{ $vendor->address ?? 'N/A' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr><th width="35%">Bank Name</th><td>{{ $vendor->bank_name ?? 'N/A' }}</td></tr>
                            <tr><th>Bank Account No</th><td>{{ $vendor->bank_account_no ?? 'N/A' }}</td></tr>
                            <tr><th>Routing Number</th><td>{{ $vendor->routing_number ?? 'N/A' }}</td></tr>
                            <tr><th>TIN / VAT No</th><td>{{ $vendor->tin_vat_no ?? 'N/A' }}</td></tr>
                            <tr>
                                <th>Services Offered</th>
                                <td>
                                    @if($vendor->services_offered && count($vendor->services_offered) > 0)
                                        @foreach($vendor->services_offered as $service)
                                            <span class="badge bg-primary fs-xs me-1">{{ ucwords(str_replace('_', ' ', $service)) }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted fs-xs">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Rating</th>
                                <td>
                                    @if($vendor->rating)
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa fa-star {{ $i <= Math.round($vendor->rating) ? 'text-warning' : 'text-muted opacity-50' }}"></i>
                                        @endfor
                                        <span class="ms-1 fs-xs">({{ number_format($vendor->rating, 1) }})</span>
                                    @else
                                        <span class="text-muted fs-xs">Unrated</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($vendor->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="row g-2 my-2">
                <div class="col-md-3">
                    <div class="block block-rounded block-fx-pop mb-0 bg-body-light text-center p-3">
                        <div class="fs-xs fw-semibold text-uppercase text-muted">Vehicle Services</div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['total_maintenances'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded block-fx-pop mb-0 bg-body-light text-center p-3">
                        <div class="fs-xs fw-semibold text-uppercase text-muted">Rent Agreements</div>
                        <div class="fs-2 fw-bold text-info">{{ $stats['total_agreements'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded block-fx-pop mb-0 bg-body-light text-center p-3">
                        <div class="fs-xs fw-semibold text-uppercase text-muted">Total Service Cost</div>
                        <div class="fs-4 fw-bold text-dark">৳ {{ number_format($stats['total_cost'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded block-fx-pop mb-0 bg-body-light text-center p-3">
                        <div class="fs-xs fw-semibold text-uppercase text-muted">Pending Invoices</div>
                        <div class="fs-2 fw-bold text-warning">{{ $stats['pending_invoices'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agreements List -->
    @if ($vendor->agreements->count() > 0)
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-file-contract text-info me-2"></i> Facilities Rent Agreements</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Agreement Code</th>
                            <th>Building</th>
                            <th>Tenure</th>
                            <th>Monthly Rent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor->agreements as $agreement)
                        <tr>
                            <td><span class="fw-semibold font-monospace fs-sm">{{ $agreement->agreement_code }}</span></td>
                            <td>{{ $agreement->building->name ?? 'N/A' }}</td>
                            <td>{{ $agreement->start_date ? $agreement->start_date->format('M Y') : 'N/A' }} - {{ $agreement->end_date ? $agreement->end_date->format('M Y') : 'N/A' }}</td>
                            <td>৳ {{ number_format($agreement->monthly_rent, 2) }}</td>
                            <td>
                                @if($agreement->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Maintenance Records -->
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-wrench text-warning me-2"></i> Maintenance &amp; Repair History</h3>
            <a href="{{ route('vendors.history', $vendor->id) }}" class="btn btn-alt-primary btn-sm float-end">View Full History</a>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->maintenances->take(10) as $maintenance)
                        <tr>
                            <td>{{ $maintenance->start_datetime ? $maintenance->start_datetime->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</td>
                            <td><span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">{{ $maintenance->getMaintenanceTypeLabel() }}</span></td>
                            <td>৳ {{ number_format($maintenance->total_service_cost, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($maintenance->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No vehicle maintenance records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="block block-rounded mt-3 mb-4">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-receipt text-success me-2"></i> Invoices</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->invoices->take(10) as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A' }}</td>
                            <td>৳ {{ number_format($invoice->total_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $invoice->getPaymentStatusBadge() }}">{{ $invoice->getPaymentStatusLabel() }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No invoices found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
