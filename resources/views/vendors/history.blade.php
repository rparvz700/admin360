@extends('Partials.app', ['activeMenu' => 'vendors'])

@section('title') Vendor Maintenance History @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-history text-primary me-2"></i> Maintenance History &mdash; {{ $vendor->name }}</h3>
            <div class="block-options">
                <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-alt-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back to Vendor Details
                </a>
            </div>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Service / Job Type</th>
                            <th>Description</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                        <tr>
                            <td>{{ $maintenance->start_datetime ? $maintenance->start_datetime->format('d M Y') : 'N/A' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</div>
                                <div class="fs-xs text-muted">{{ $maintenance->vehicle->model ?? '' }}</div>
                            </td>
                            <td><span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">{{ $maintenance->getMaintenanceTypeLabel() }}</span></td>
                            <td>{{ Str::limit($maintenance->description ?? 'N/A', 60) }}</td>
                            <td>৳ {{ number_format($maintenance->total_service_cost, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($maintenance->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No maintenance history records found for this vendor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $maintenances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
