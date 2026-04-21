@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Vehicle-Wise Cost Report @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle-Wise Cost Report</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Filters -->
            <form method="GET" action="{{ route('maintenance.reports.vehicle-cost') }}" class="mb-4">
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
                            <th>Vehicle</th>
                            <th class="text-center">Total Maintenances</th>
                            <th class="text-end">Routine Cost</th>
                            <th class="text-end">Breakdown Cost</th>
                            <th class="text-end">Accident Cost</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $data)
                        <tr>
                            <td>
                                <strong>{{ $data['vehicle']->registration_number }}</strong><br>
                                <small class="text-muted">{{ $data['vehicle']->brand }} {{ $data['vehicle']->model }}</small>
                            </td>
                            <td class="text-center">{{ $data['total_maintenances'] }}</td>
                            <td class="text-end">৳ {{ number_format($data['routine_cost'], 2) }}</td>
                            <td class="text-end">৳ {{ number_format($data['breakdown_cost'], 2) }}</td>
                            <td class="text-end">৳ {{ number_format($data['accident_cost'], 2) }}</td>
                            <td class="text-end"><strong>৳ {{ number_format($data['total_cost'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No data available for selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($vehicles->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total:</th>
                            <th class="text-center">{{ $vehicles->sum('total_maintenances') }}</th>
                            <th class="text-end">৳ {{ number_format($vehicles->sum('routine_cost'), 2) }}</th>
                            <th class="text-end">৳ {{ number_format($vehicles->sum('breakdown_cost'), 2) }}</th>
                            <th class="text-end">৳ {{ number_format($vehicles->sum('accident_cost'), 2) }}</th>
                            <th class="text-end"><strong>৳ {{ number_format($vehicles->sum('total_cost'), 2) }}</strong></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection