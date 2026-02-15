@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Vendor Comparison Report @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vendor Comparison Report</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Filters -->
            <form method="GET" action="{{ route('maintenance.reports.vendor-comparison') }}" class="mb-4">
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
                            <th class="text-center">Routine</th>
                            <th class="text-center">Breakdown</th>
                            <th class="text-center">Vehicles Serviced</th>
                            <th class="text-end">Average Cost</th>
                            <th class="text-end">Total Cost</th>
                            <th class="text-center">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $data)
                        <tr>
                            <td>
                                <strong>{{ $data['vendor']->name }}</strong><br>
                                <small class="text-muted">{{ $data['vendor']->vendor_code }}</small>
                            </td>
                            <td class="text-center">{{ $data['total_services'] }}</td>
                            <td class="text-center">{{ $data['routine_services'] }}</td>
                            <td class="text-center">{{ $data['breakdown_services'] }}</td>
                            <td class="text-center">{{ $data['vehicles_serviced'] }}</td>
                            <td class="text-end">৳ {{ number_format($data['avg_cost'], 2) }}</td>
                            <td class="text-end"><strong>৳ {{ number_format($data['total_cost'], 2) }}</strong></td>
                            <td class="text-center">
                                @if($data['avg_rating'])
                                    <span class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $data['avg_rating'])
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </span>
                                    <br>
                                    <small>{{ number_format($data['avg_rating'], 1) }}/5</small>
                                @else
                                    <span class="text-muted">No Rating</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No vendor data available for selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($vendors->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total:</th>
                            <th class="text-center">{{ $vendors->sum('total_services') }}</th>
                            <th class="text-center">{{ $vendors->sum('routine_services') }}</th>
                            <th class="text-center">{{ $vendors->sum('breakdown_services') }}</th>
                            <th class="text-center">-</th>
                            <th class="text-end">-</th>
                            <th class="text-end"><strong>৳ {{ number_format($vendors->sum('total_cost'), 2) }}</strong></th>
                            <th class="text-center">-</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($vendors->count() > 0)
            <div class="alert alert-info mt-3">
                <i class="fa fa-info-circle"></i>
                <strong>Analysis:</strong> Comparing {{ $vendors->count() }} vendor(s) based on performance metrics.
                Best average cost: ৳ {{ number_format($vendors->min('avg_cost'), 2) }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection