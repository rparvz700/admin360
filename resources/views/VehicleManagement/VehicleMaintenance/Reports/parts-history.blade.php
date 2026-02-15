@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Parts Replacement History @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Parts Replacement History</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Filters -->
            <form method="GET" action="{{ route('maintenance.reports.parts-history') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="vehicle_id">Vehicle</label>
                        <select class="form-select" id="vehicle_id" name="vehicle_id">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="part_id">Part</label>
                        <select class="form-select" id="part_id" name="part_id">
                            <option value="">All Parts</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}" {{ $partId == $part->id ? 'selected' : '' }}>
                                    {{ $part->part_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="block block-rounded">
                        <div class="block-content">
                            <p class="fs-sm fw-medium text-muted mb-0">Total Replacements</p>
                            <p class="fs-3 fw-bold mb-0">{{ $stats['total_replacements'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded">
                        <div class="block-content">
                            <p class="fs-sm fw-medium text-muted mb-0">Total Repairs</p>
                            <p class="fs-3 fw-bold mb-0">{{ $stats['total_repairs'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded">
                        <div class="block-content">
                            <p class="fs-sm fw-medium text-muted mb-0">Total Cost</p>
                            <p class="fs-3 fw-bold mb-0">৳ {{ number_format($stats['total_cost'], 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="block block-rounded">
                        <div class="block-content">
                            <p class="fs-sm fw-medium text-muted mb-0">Under Warranty</p>
                            <p class="fs-3 fw-bold mb-0 text-success">{{ $stats['under_warranty'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Part</th>
                            <th>Action</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Cost</th>
                            <th>Vendor</th>
                            <th>Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partsHistory as $history)
                        <tr>
                            <td>{{ $history->created_at->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $history->vehicle->registration_number ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                {{ $history->part->part_name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $history->part->part_code ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $history->getActionTypeBadge() }}">
                                    {{ $history->getActionTypeLabel() }}
                                </span>
                            </td>
                            <td class="text-center">{{ $history->quantity }}</td>
                            <td class="text-end">৳ {{ number_format($history->part_cost, 2) }}</td>
                            <td>{{ $history->vendor->name ?? 'N/A' }}</td>
                            <td>
                                @if($history->warranty_expiry_date)
                                    @if($history->isUnderWarranty())
                                        <span class="badge bg-success">Valid until {{ $history->warranty_expiry_date->format('d M Y') }}</span>
                                    @else
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                @else
                                    <span class="text-muted">No Warranty</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No parts history found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $partsHistory->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection