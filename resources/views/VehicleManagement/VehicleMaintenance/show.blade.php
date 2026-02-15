@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Maintenance Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Maintenance Details</h3>
            <div>
                <a href="{{ route('maintenance.maintenances.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                <a href="{{ route('maintenance.maintenances.edit', $maintenance->id) }}" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="30%">Vehicle</th>
                        <td>{{ $maintenance->vehicle->registration_number ?? 'N/A' }} - {{ $maintenance->vehicle->brand ?? '' }} {{ $maintenance->vehicle->model ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Maintenance Type</th>
                        <td>
                            <span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">
                                {{ $maintenance->getMaintenanceTypeLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Vendor</th>
                        <td>{{ $maintenance->vendor->name ?? 'N/A' }} ({{ $maintenance->vendor->vendor_code ?? '' }})</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($maintenance->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Start Date & Time</th>
                        <td>{{ $maintenance->start_datetime->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Estimated End</th>
                        <td>{{ $maintenance->estimated_end_datetime ? $maintenance->estimated_end_datetime->format('d M Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Actual End</th>
                        <td>{{ $maintenance->actual_end_datetime ? $maintenance->actual_end_datetime->format('d M Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Service Description</th>
                        <td>{{ $maintenance->service_description }}</td>
                    </tr>
                    <tr>
                        <th>Meter Reading</th>
                        <td>{{ $maintenance->meter_reading_at_service ? number_format($maintenance->meter_reading_at_service) . ' km' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Labor Cost</th>
                        <td>৳ {{ number_format($maintenance->labor_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Parts Cost</th>
                        <td>৳ {{ number_format($maintenance->parts_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Cost</th>
                        <td><strong>৳ {{ number_format($maintenance->total_service_cost, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Performed By</th>
                        <td>{{ $maintenance->performed_by ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Next Service Due Date</th>
                        <td>{{ $maintenance->next_service_due_date ? $maintenance->next_service_due_date->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Next Service Due KM</th>
                        <td>{{ $maintenance->next_service_due_km ? number_format($maintenance->next_service_due_km) . ' km' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Service Completed</th>
                        <td>{{ $maintenance->current_service_completed ? 'Yes' : 'No' }}</td>
                    </tr>
                    <tr>
                        <th>Approved By</th>
                        <td>{{ $maintenance->approver->name ?? 'Not Approved' }}</td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td>{{ $maintenance->remarks ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Parts Used -->
    @if($maintenance->maintenanceParts->count() > 0)
    <div class="block block-rounded mt-3">
        <div class="block-header block-header-default">
            <h3 class="block-title">Parts Used/Replaced</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Part Name</th>
                            <th>Part Code</th>
                            <th>Action</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>Tyre Position</th>
                            <th>Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenance->maintenanceParts as $part)
                        <tr>
                            <td>{{ $part->part->part_name ?? 'N/A' }}</td>
                            <td>{{ $part->part->part_code ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $part->getActionTypeBadge() }}">
                                    {{ $part->getActionTypeLabel() }}
                                </span>
                            </td>
                            <td>{{ $part->quantity }}</td>
                            <td>৳ {{ number_format($part->part_cost, 2) }}</td>
                            <td>{{ $part->tyre_position ?? 'N/A' }}</td>
                            <td>
                                @if($part->warranty_expiry_date)
                                    {{ $part->warranty_expiry_date->format('d M Y') }}
                                    @if($part->isUnderWarranty())
                                        <span class="badge bg-success">Active</span>
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
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total Parts Cost:</th>
                            <th colspan="3">৳ {{ number_format($maintenance->parts_cost, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection