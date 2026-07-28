@extends('Partials.app', ['activeMenu' => 'electricity-reports'])

@section('title') Electricity Reports & Analytics @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-chart-line text-success me-2"></i> Electricity Reports & Analytics Dashboard</h3>
        </div>
        <div class="block-content">

            <!-- Filter Toolbar -->
            <form method="GET" action="{{ route('electricity.reports.index') }}" class="row mb-4 bg-body-light p-3 rounded mx-0 border" id="report-filter-form">
                <div class="col-md-4">
                    <label class="form-label fs-sm fw-semibold" for="rio_id">Filter by RIO Zone</label>
                    <select class="form-select select2" id="rio_id" name="rio_id" style="width: 100%;">
                        <option value="all">All RIO Zones</option>
                        @foreach($rios as $rio)
                            <option value="{{ $rio->id }}" {{ $selectedRio == $rio->id ? 'selected' : '' }}>
                                {{ $rio->name }} ({{ $rio->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <!-- Top Metric Cards -->
            <div class="row text-center mb-4">
                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-primary">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-primary">৳ {{ number_format($siteCostings->sum('total_cost'), 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Total Expenditure</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-info">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-info">{{ number_format($siteCostings->sum('total_units'), 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Total Units (kWh)</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-warning">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-warning">{{ $paymentStatusSummary->get('generated')->count ?? 0 }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Pending Payments</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-success">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-success">{{ $paymentStatusSummary->get('paid')->count ?? 0 }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Paid Requisitions</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Section 1: Per-Site Costing Report -->
            <div class="block block-rounded block-bordered mb-4">
                <div class="block-header block-header-default">
                    <h4 class="block-title fs-base"><i class="fa fa-building text-primary me-2"></i> Per-Site Costing Report</h4>
                </div>
                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter table-hover w-100">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">Site / POP Name</th>
                                    <th class="text-nowrap">Site Code</th>
                                    <th class="text-nowrap">RIO Zone</th>
                                    <th class="text-center text-nowrap">Total Requisitions</th>
                                    <th class="text-end text-nowrap">Units Consumed (kWh)</th>
                                    <th class="text-end text-nowrap">Total Expenditure (BDT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siteCostings as $row)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $row->building->site_name ?? 'N/A' }}</td>
                                    <td>{{ $row->building->code ?? $row->building->site_code ?? 'N/A' }}</td>
                                    <td>{{ $row->building->rio->name ?? 'N/A' }}</td>
                                    <td class="text-center"><span class="badge bg-secondary-light text-secondary fw-semibold">{{ $row->total_bills }}</span></td>
                                    <td class="text-end fw-semibold text-info">{{ number_format($row->total_units, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">৳ {{ number_format($row->total_cost, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4"><i class="fa fa-folder-open fa-2x mb-2 d-block opacity-50"></i>No site costing data found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Report Section 2: RIO Monthly Summary & Trend -->
            <div class="row">
                <div class="col-md-7 mb-4">
                    <div class="block block-rounded block-bordered h-100 mb-0">
                        <div class="block-header block-header-default">
                            <h4 class="block-title fs-base"><i class="fa fa-map-marked-alt text-info me-2"></i> RIO Regional Summary</h4>
                        </div>
                        <div class="block-content block-content-full">
                            <div class="table-responsive">
                                <table class="table table-sm table-vcenter table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">RIO Name</th>
                                            <th class="text-nowrap">Code</th>
                                            <th class="text-center text-nowrap">Bills</th>
                                            <th class="text-end text-nowrap">Units (kWh)</th>
                                            <th class="text-end text-nowrap">Total Amount (BDT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rioSummary as $row)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $row->rio->name ?? 'Unassigned RIO' }}</td>
                                            <td>{{ $row->rio->code ?? 'N/A' }}</td>
                                            <td class="text-center"><span class="badge bg-info-light text-info fw-semibold">{{ $row->bill_count }}</span></td>
                                            <td class="text-end">{{ number_format($row->total_units, 2) }}</td>
                                            <td class="text-end fw-bold text-primary">৳ {{ number_format($row->total_amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4"><i class="fa fa-map-marked-alt fa-2x mb-2 d-block opacity-50"></i>No RIO summary data.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Section 3: Month-over-Month Trend -->
                <div class="col-md-5 mb-4">
                    <div class="block block-rounded block-bordered h-100 mb-0">
                        <div class="block-header block-header-default">
                            <h4 class="block-title fs-base"><i class="fa fa-history text-warning me-2"></i> Month-over-Month Trend</h4>
                        </div>
                        <div class="block-content block-content-full">
                            <div class="table-responsive">
                                <table class="table table-sm table-vcenter table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">Bill Month</th>
                                            <th class="text-end text-nowrap">Units (kWh)</th>
                                            <th class="text-end text-nowrap">Cost (BDT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyTrends as $trend)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $trend->billing_month }}</td>
                                            <td class="text-end text-info fw-semibold">{{ number_format($trend->total_units, 2) }}</td>
                                            <td class="text-end fw-bold text-primary">৳ {{ number_format($trend->total_amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4"><i class="fa fa-history fa-2x mb-2 d-block opacity-50"></i>No trend data.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            $('#rio_id').on('change', function() {
                $('#report-filter-form').submit();
            });
        });
    </script>
@endsection
