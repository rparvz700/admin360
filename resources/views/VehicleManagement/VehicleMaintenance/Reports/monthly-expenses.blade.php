@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])
@section('title') Monthly Expenses Report @endsection
@section('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Monthly Expenses Report</h3>
            <a href="{{ route('maintenance.reports.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left"></i> Reports List
            </a>
        </div>
        <div class="block-content">
            <!-- Year Filter -->
            <form method="GET" action="{{ route('maintenance.reports.monthly-expenses') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="year">Year</label>
                        <select class="form-select" id="year" name="year">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Monthly Chart -->
            <div class="mb-4">
                <canvas id="monthlyExpensesChart" height="100"></canvas>
            </div>

            <!-- Monthly Breakdown Table -->
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Maintenance Count</th>
                            <th class="text-end">Labor Cost</th>
                            <th class="text-end">Parts Cost</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        @endphp
                        @foreach($allMonths as $data)
                        <tr>
                            <td><strong>{{ $monthNames[$data->month - 1] }} {{ $year }}</strong></td>
                            <td class="text-center">{{ $data->maintenance_count }}</td>
                            <td class="text-end">৳ {{ number_format($data->total_labor, 2) }}</td>
                            <td class="text-end">৳ {{ number_format($data->total_parts, 2) }}</td>
                            <td class="text-end"><strong>৳ {{ number_format($data->total_cost, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total:</th>
                            <th class="text-center">{{ $allMonths->sum('maintenance_count') }}</th>
                            <th class="text-end">৳ {{ number_format($allMonths->sum('total_labor'), 2) }}</th>
                            <th class="text-end">৳ {{ number_format($allMonths->sum('total_parts'), 2) }}</th>
                            <th class="text-end"><strong>৳ {{ number_format($allMonths->sum('total_cost'), 2) }}</strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Vendor Breakdown -->
            <h5 class="mb-3">Vendor-Wise Breakdown for {{ $year }}</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendorExpenses as $vendor)
                        <tr>
                            <td><strong>{{ $vendor->name }}</strong></td>
                            <td class="text-end">৳ {{ number_format($vendor->total_cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">No vendor expenses for this year</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($vendorExpenses->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total:</th>
                            <th class="text-end"><strong>৳ {{ number_format($vendorExpenses->sum('total_cost'), 2) }}</strong></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('monthlyExpensesChart').getContext('2d');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'Labor Cost',
                data: {!! json_encode($allMonths->pluck('total_labor')->values()) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Parts Cost',
                data: {!! json_encode($allMonths->pluck('total_parts')->values()) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳ ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ৳ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endsection