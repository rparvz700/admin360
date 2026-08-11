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
            <div class="block-options">
                <button type="button" onclick="window.print();" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-print me-1"></i> Print Analytics Summary
                </button>
            </div>
        </div>
        <div class="block-content">

            <!-- Comprehensive Filter Toolbar -->
            <form method="GET" action="{{ route('electricity.reports.index') }}" class="row mb-4 bg-body-light p-3 rounded mx-0 border align-items-end" id="report-filter-form">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="building_id">Filter by Site / POP</label>
                    <select class="form-select select2" id="building_id" name="building_id" style="width: 100%;">
                        <option value="all">All Sites & POPs</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" {{ $selectedBuilding == $building->id ? 'selected' : '' }}>
                                {{ $building->site_name }} {{ ($building->code ?? $building->site_code) ? "({$building->code})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="project_name">Filter by Project</label>
                    <select class="form-select select2" id="project_name" name="project_name" style="width: 100%;">
                        <option value="all">All Projects</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->name }}" {{ $selectedProject == $proj->name ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-sm fw-semibold" for="bill_type">Bill Type</label>
                    <select class="form-select select2" id="bill_type" name="bill_type" style="width: 100%;">
                        <option value="all">All Types</option>
                        <option value="postpaid" {{ $selectedBillType == 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                        <option value="prepaid" {{ $selectedBillType == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-sm fw-semibold" for="status">Payment Status</label>
                    <select class="form-select select2" id="status" name="status" style="width: 100%;">
                        <option value="all">All Statuses</option>
                        <option value="generated" {{ $selectedStatus == 'generated' ? 'selected' : '' }}>Pending Payment</option>
                        <option value="paid" {{ $selectedStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
            </form>

            <!-- Top Metric Stat Cards -->
            <div class="row text-center mb-4">
                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-primary">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-primary">৳ {{ number_format($totalExpenditure, 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Total Expenditure</div>
                            <div class="fs-xs text-muted mt-1">{{ number_format($totalBillsCount) }} Bills</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-info">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-info">{{ number_format($totalUnits, 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Total Units (kWh)</div>
                            <div class="fs-xs text-muted mt-1">Base: ৳ {{ number_format($totalBaseAmount, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-warning">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-warning">৳ {{ number_format($pendingAmount, 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Pending Payments</div>
                            <div class="fs-xs text-warning fw-semibold mt-1">{{ $pendingCount }} Bills Pending</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3 mb-3">
                    <div class="block block-rounded block-fx-shadow h-100 mb-0 border-start border-4 border-success">
                        <div class="block-content block-content-full">
                            <div class="fs-2 fw-bold text-success">৳ {{ number_format($paidAmount, 2) }}</div>
                            <div class="fs-sm fw-semibold text-muted text-uppercase">Paid Amount</div>
                            <div class="fs-xs text-success fw-semibold mt-1">{{ $paidCount }} Bills Settled</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Section 1: Site Expenditure Summary -->
            <div class="block block-rounded block-bordered mb-4">
                <div class="block-header block-header-default">
                    <h4 class="block-title fs-base"><i class="fa fa-building text-primary me-2"></i> Per-Site Expenditure Summary</h4>
                </div>
                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter table-hover w-100">
                            <thead>
                                <tr class="table-light">
                                    <th class="text-nowrap">Site / POP Name</th>
                                    <th class="text-nowrap">Site Code</th>
                                    <th class="text-center text-nowrap">Total Bills</th>
                                    <th class="text-end text-nowrap">Units Consumed (kWh)</th>
                                    <th class="text-end text-nowrap">Base Amount (BDT)</th>
                                    <th class="text-end text-nowrap">VAT & Fees (BDT)</th>
                                    <th class="text-end text-nowrap">Total Expenditure (BDT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siteCostings as $row)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $row->building->site_name ?? 'N/A' }}</td>
                                    <td>{{ $row->building->code ?? $row->building->site_code ?? 'N/A' }}</td>
                                    <td class="text-center"><span class="badge bg-secondary-light text-secondary fw-semibold">{{ $row->total_bills }}</span></td>
                                    <td class="text-end fw-semibold text-info">{{ number_format($row->total_units, 2) }}</td>
                                    <td class="text-end">৳ {{ number_format($row->total_net, 2) }}</td>
                                    <td class="text-end text-warning">৳ {{ number_format($row->total_vat, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">৳ {{ number_format($row->total_cost, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4"><i class="fa fa-folder-open fa-2x mb-2 d-block opacity-50"></i>No site expenditure data found for the selected filter.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Report Section 2 & 3: Project Summary & Monthly Trend -->
            <div class="row">
                <!-- Project Summary -->
                <div class="col-md-6 mb-4">
                    <div class="block block-rounded block-bordered h-100 mb-0">
                        <div class="block-header block-header-default">
                            <h4 class="block-title fs-base"><i class="fa fa-folder text-info me-2"></i> Project-wise Cost Analysis</h4>
                        </div>
                        <div class="block-content block-content-full">
                            <div class="table-responsive">
                                <table class="table table-sm table-vcenter table-hover w-100">
                                    <thead>
                                        <tr class="table-light">
                                            <th class="text-nowrap">Project Name</th>
                                            <th class="text-center text-nowrap">Bills</th>
                                            <th class="text-end text-nowrap">Units (kWh)</th>
                                            <th class="text-end text-nowrap">Total Amount (BDT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($projectSummary as $row)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $row->project_name }}</td>
                                            <td class="text-center"><span class="badge bg-info-light text-info fw-semibold">{{ $row->total_bills }}</span></td>
                                            <td class="text-end text-info fw-semibold">{{ number_format($row->total_units, 2) }}</td>
                                            <td class="text-end fw-bold text-primary">৳ {{ number_format($row->total_cost, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4"><i class="fa fa-folder-open fa-2x mb-2 d-block opacity-50"></i>No project summary data available.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend -->
                <div class="col-md-6 mb-4">
                    <div class="block block-rounded block-bordered h-100 mb-0">
                        <div class="block-header block-header-default">
                            <h4 class="block-title fs-base"><i class="fa fa-history text-warning me-2"></i> Monthly Expenditure & Consumption Trend</h4>
                        </div>
                        <div class="block-content block-content-full">
                            <div class="table-responsive">
                                <table class="table table-sm table-vcenter table-hover w-100">
                                    <thead>
                                        <tr class="table-light">
                                            <th class="text-nowrap">Month</th>
                                            <th class="text-end text-nowrap">Postpaid (BDT)</th>
                                            <th class="text-end text-nowrap">Prepaid (BDT)</th>
                                            <th class="text-end text-nowrap">Total Units</th>
                                            <th class="text-end text-nowrap">Total (BDT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyTrends as $trend)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $trend->billing_month }}</td>
                                            <td class="text-end text-muted">৳ {{ number_format($trend->postpaid_cost, 2) }}</td>
                                            <td class="text-end text-muted">৳ {{ number_format($trend->prepaid_cost, 2) }}</td>
                                            <td class="text-end text-info fw-semibold">{{ number_format($trend->total_units, 2) }}</td>
                                            <td class="text-end fw-bold text-primary">৳ {{ number_format($trend->total_cost, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4"><i class="fa fa-history fa-2x mb-2 d-block opacity-50"></i>No monthly trend data available.</td>
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

            $('#building_id, #project_name, #bill_type, #status').on('change', function() {
                $('#report-filter-form').submit();
            });
        });
    </script>
@endsection
