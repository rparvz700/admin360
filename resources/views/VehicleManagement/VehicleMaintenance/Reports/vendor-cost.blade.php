@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Vendor-Wise Cost Report @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vendor-Wise Cost Report</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Filters -->
            <form method="GET" action="{{ route('maintenance.reports.vendor-cost') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor</th>
                            <th class="text-center">Total Services</th>
                            <th class="text-center">Vehicles Serviced</th>
                            <th class="text-end">Average Cost</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $data)
                        <tr>
                            <td>
                                <strong>{{ $data['vendor']->name }}</strong><br>
                                <small class="text-muted">{{ $data['vendor']->vendor_code }}</small>
                            </td>
                            <td class="text-center">{{ $data['total_maintenances'] }}</td>
                            <td class="text-center">{{ $data['vehicles_serviced'] }}</td>
                            <td class="text-end">৳ {{ number_format($data['avg_cost'], 2) }}</td>
                            <td class="text-end"><strong>৳ {{ number_format($data['total_cost'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No data available for selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($vendors->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total:</th>
                            <th class="text-center">{{ $vendors->sum('total_maintenances') }}</th>
                            <th class="text-center">-</th>
                            <th class="text-end">-</th>
                            <th class="text-end"><strong>৳ {{ number_format($vendors->sum('total_cost'), 2) }}</strong></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection