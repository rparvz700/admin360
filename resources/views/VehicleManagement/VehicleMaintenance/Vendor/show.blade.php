@extends('Partials.app', ['activeMenu' => 'vendors'])
@section('title') Vendor Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vendor Details</h3>
            <div>
                <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                <a href="{{ route('maintenance.vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th width="25%">Vendor Code</th><td>{{ $vendor->vendor_code }}</td></tr>
                    <tr><th>Name</th><td>{{ $vendor->name }}</td></tr>
                    <tr><th>Type</th><td>{{ $vendor->getVendorTypeLabel() }}</td></tr>
                    <tr><th>Contact Person</th><td>{{ $vendor->contact_person ?? 'N/A' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $vendor->phone }}</td></tr>
                    <tr><th>Email</th><td>{{ $vendor->email ?? 'N/A' }}</td></tr>
                    <tr><th>Address</th><td>{{ $vendor->address ?? 'N/A' }}</td></tr>
                    <tr>
                        <th>Services Offered</th>
                        <td>
                            @if($vendor->services_offered && count($vendor->services_offered) > 0)
                                @foreach($vendor->services_offered as $service)
                                    <span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', $service)) }}</span>
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Rating</th>
                        <td>
                            @if($vendor->rating)
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $vendor->rating)
                                        ⭐
                                    @else
                                        ☆
                                    @endif
                                @endfor
                                ({{ number_format($vendor->rating, 1) }})
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr><th>Total Services</th><td>{{ $stats['total_maintenances'] }}</td></tr>
                    <tr><th>Total Cost</th><td>৳ {{ number_format($stats['total_cost'], 2) }}</td></tr>
                    <tr><th>Pending Invoices</th><td>{{ $stats['pending_invoices'] }}</td></tr>
                    <tr><th>Overdue Invoices</th><td>{{ $stats['overdue_invoices'] }}</td></tr>
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

    <!-- Recent Maintenance Records -->
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title">Recent Maintenance Records</h3>
            <a href="{{ route('maintenance.vendors.history', $vendor->id) }}" class="btn btn-primary btn-sm float-end">View All History</a>
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
                            <td>{{ $maintenance->start_datetime->format('d M Y') }}</td>
                            <td>{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</td>
                            <td><span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">{{ $maintenance->getMaintenanceTypeLabel() }}</span></td>
                            <td>৳ {{ number_format($maintenance->total_service_cost, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($maintenance->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No maintenance records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title">Recent Invoices</h3>
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
                            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td>৳ {{ number_format($invoice->total_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $invoice->getPaymentStatusBadge() }}">{{ $invoice->getPaymentStatusLabel() }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No invoices found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection