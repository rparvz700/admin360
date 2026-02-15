@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Part Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Part Details</h3>
            <div>
                <a href="{{ route('maintenance.parts.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                <a href="{{ route('maintenance.parts.edit', $part->id) }}" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th width="30%">Part Code</th><td>{{ $part->part_code }}</td></tr>
                    <tr><th>Part Name</th><td>{{ $part->part_name }}</td></tr>
                    <tr>
                        <th>Category</th>
                        <td>
                            <span class="badge bg-{{ $part->getCategoryBadge() }}">
                                {{ $part->getCategoryLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Description</th><td>{{ $part->description ?? 'N/A' }}</td></tr>
                    <tr>
                        <th>Typical Lifespan (KM)</th>
                        <td>{{ $part->typical_lifespan_km ? number_format($part->typical_lifespan_km) . ' km' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Typical Lifespan (Months)</th>
                        <td>{{ $part->typical_lifespan_months ? $part->typical_lifespan_months . ' months' : 'N/A' }}</td>
                    </tr>
                    <tr><th>Total Replacements</th><td>{{ $stats['total_replacements'] }}</td></tr>
                    <tr><th>Total Repairs</th><td>{{ $stats['total_repairs'] }}</td></tr>
                    <tr><th>Total Services</th><td>{{ $stats['total_services'] }}</td></tr>
                    <tr><th>Total Cost</th><td>৳ {{ number_format($stats['total_cost'], 2) }}</td></tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($part->is_active)
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

    <!-- Usage History -->
    @if($part->maintenanceParts->count() > 0)
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title">Usage History</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Action Type</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>Vendor</th>
                            <th>Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($part->maintenanceParts->take(20) as $usage)
                        <tr>
                            <td>{{ $usage->created_at->format('d M Y') }}</td>
                            <td>{{ $usage->vehicle->registration_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $usage->getActionTypeBadge() }}">
                                    {{ $usage->getActionTypeLabel() }}
                                </span>
                            </td>
                            <td>{{ $usage->quantity }}</td>
                            <td>৳ {{ number_format($usage->part_cost, 2) }}</td>
                            <td>{{ $usage->vendor->name ?? 'N/A' }}</td>
                            <td>
                                @if($usage->warranty_expiry_date)
                                    @if($usage->isUnderWarranty())
                                        <span class="badge bg-success">Valid until {{ $usage->warranty_expiry_date->format('d M Y') }}</span>
                                    @else
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                @else
                                    <span class="text-muted">No Warranty</span>
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
</div>
@endsection