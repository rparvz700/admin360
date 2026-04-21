@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Vendor Monthly Bill Report @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vendor Monthly Bill Report</h3>
        </div>
        <div class="block-content">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('maintenance.reports.vendor-bill') }}" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="vendor_id">Vendor <span class="text-danger">*</span></label>
                        <select class="form-select" id="vendor_id" name="vendor_id" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }} ({{ $vendor->vendor_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="month">Month <span class="text-danger">*</span></label>
                        <select class="form-select" id="month" name="month">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="year">Year <span class="text-danger">*</span></label>
                        <select class="form-select" id="year" name="year">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="payment_type">Payment Type</label>
                        <input type="text" class="form-control" id="payment_type" name="payment_type"
                               value="{{ request('payment_type', 'BEFTN') }}">
                    </div>
                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search me-1"></i> Generate Report
                        </button>
                        @if(isset($rows) && $rows->count())
                            <button type="submit" form="exportForm" class="btn btn-success ms-2">
                                <i class="fa fa-file-excel me-1"></i> Export to Excel
                            </button>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Hidden export form -->
            <form id="exportForm" method="GET" action="{{ route('maintenance.reports.vendor-bill.export') }}">
                <input type="hidden" name="vendor_id"    value="{{ request('vendor_id') }}">
                <input type="hidden" name="month"        value="{{ request('month', date('n')) }}">
                <input type="hidden" name="year"         value="{{ request('year', date('Y')) }}">
                <input type="hidden" name="payment_type" value="{{ request('payment_type', 'BEFTN') }}">
            </form>

            @if(isset($rows) && $rows->count())
            <!-- Report Preview -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-vcenter" style="font-size: 12px;">
                    <thead>
                        <tr class="table-dark text-center">
                            <td colspan="20" class="fw-bold fs-6 py-2">Vehicle Maintenance</td>
                        </tr>
                        <tr class="table-secondary">
                            <td colspan="20">Payment Type: {{ $paymentType }}</td>
                        </tr>
                        <tr class="table-secondary">
                            <td colspan="20">Vendor Name: {{ $vendor->name }}</td>
                        </tr>
                        <tr class="table-secondary">
                            <td colspan="14">Bill for the month of {{ $monthName }}-{{ $year }}</td>
                            <td colspan="6" class="text-end">Date: {{ now()->format('d.m.Y') }}</td>
                        </tr>
                        <tr class="table-light text-center fw-bold" style="font-size: 11px;">
                            <th>S/l</th>
                            <th>Date</th>
                            <th>Particulars</th>
                            <th>Vehicle No</th>
                            <th>Vehicle Type</th>
                            <th>Reg. Year</th>
                            <th>Engine CC</th>
                            <th>Vehicle Weight</th>
                            <th>Vehicle Location</th>
                            <th>Present KM</th>
                            <th>Previous KM</th>
                            <th>Consumption</th>
                            <th>Vo-Ref-No</th>
                            <th>Price</th>
                            <th>Qty.</th>
                            <th>Taka</th>
                            <th>VAT Rate</th>
                            <th>VAT Amount</th>
                            <th>Total Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="text-center">{{ $row['sl'] }}</td>
                            <td class="text-center text-nowrap">{{ $row['date'] }}</td>
                            <td>{{ $row['particulars'] }}</td>
                            <td class="text-center text-nowrap">{{ $row['vehicle_no'] }}</td>
                            <td class="text-center">{{ $row['vehicle_type'] }}</td>
                            <td class="text-center">{{ $row['reg_year'] }}</td>
                            <td class="text-center">{{ $row['engine_cc'] }}</td>
                            <td class="text-center">{{ $row['vehicle_weight'] }}</td>
                            <td class="text-center">{{ $row['location'] }}</td>
                            <td class="text-end">{{ $row['present_km'] }}</td>
                            <td class="text-end">{{ $row['previous_km'] }}</td>
                            <td class="text-end">{{ $row['consumption'] }}</td>
                            <td class="text-center">{{ $row['vo_ref_no'] }}</td>
                            <td class="text-end">{{ $row['price'] }}</td>
                            <td class="text-center">{{ $row['qty'] }}</td>
                            <td class="text-end">{{ number_format($row['taka'], 2) }}</td>
                            <td class="text-center">{{ $row['vat_rate'] ? ($row['vat_rate'] * 100) . '%' : '' }}</td>
                            <td class="text-end">{{ number_format($row['vat_amount'], 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($row['total_amount'], 2) }}</td>
                            <td class="text-center">{{ $row['remarks'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="15" class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($totals['taka'], 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totals['vat_amount'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total_amount'], 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="20" class="pt-2">
                                <strong>Amount in words:</strong> {{ $amountInWords }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @elseif(request('vendor_id'))
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-1"></i>
                No maintenance records found for the selected vendor and month.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection