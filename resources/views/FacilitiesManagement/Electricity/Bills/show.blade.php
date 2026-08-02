@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title') Electricity Bill Details: {{ $bill->requisition_no }} @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">
                <i class="fa fa-file-invoice text-primary me-2"></i> Electricity Bill Details: <span class="fw-bold">{{ $bill->requisition_no }}</span>
            </h3>
            <div class="block-options">
                <a href="{{ route('electricity.bills.print', $bill->id) }}" target="_blank" class="btn btn-sm btn-alt-secondary me-2">
                    <i class="fa fa-print me-1"></i> Print Electricity Bill
                </a>
                @if($bill->status === 'generated')
                    <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#modal-mark-paid">
                        <i class="fa fa-check-circle me-1"></i> Record Payment
                    </button>
                @endif
                <a href="{{ route('electricity.bills.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="block-content">
            <div class="row">
                <!-- Main Details Card -->
                <div class="col-md-6 mb-4">
                    <h4 class="fw-light mb-3">Bill & Site Info</h4>
                    <table class="table table-striped table-bordered fs-sm">
                        <tbody>
                            <tr>
                                <th style="width: 35%;">Bill Ref No</th>
                                <td class="fw-bold text-primary">{{ $bill->requisition_no }}</td>
                            </tr>
                            <tr>
                                <th>Project Name</th>
                                <td>{{ $bill->project_name }}</td>
                            </tr>
                            <tr>
                                <th>Site / POP Name</th>
                                <td>{{ $bill->building->site_name ?? 'N/A' }} {{ ($bill->building->code ?? $bill->building->site_code) ? "(" . ($bill->building->code ?? $bill->building->site_code) . ")" : '' }}</td>
                            </tr>
                            <tr>
                                <th>Land Owner Name</th>
                                <td class="fw-semibold text-dark">{{ $landOwnerName }}</td>
                            </tr>
                            {{-- <tr>
                                <th>RIO Zone</th>
                                <td>{{ $bill->rio->name ?? ($bill->building->rio->name ?? 'N/A') }}</td>
                            </tr> --}}
                            <tr>
                                <th>Bill Month</th>
                                <td><span class="badge bg-info">{{ $bill->billing_month }}</span></td>
                            </tr>
                            <tr>
                                <th>Received Sub-Centre Date</th>
                                <td>{{ $bill->received_subcenter_date ? $bill->received_subcenter_date->format('d M Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Payment / Due Date</th>
                                <td>{{ $bill->last_payment_date ? $bill->last_payment_date->format('d M Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>{{ $bill->creator->name ?? 'System' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Meter & Payment Info Card -->
                <div class="col-md-6 mb-4">
                    <h4 class="fw-light mb-3">Meter & Payment Info</h4>
                    <table class="table table-striped table-bordered fs-sm">
                        <tbody>
                            <tr>
                                <th style="width: 35%;">Meter Number</th>
                                <td>{{ $bill->meter->meter_number ?? 'N/A' }} (<span class="badge bg-{{ $bill->meter->meter_type_badge }}">{{ $bill->meter->meter_type_label }}</span>)</td>
                            </tr>
                            <tr>
                                <th>Consumer / Account No</th>
                                <td>{{ $bill->meter->consumer_no ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Utility Provider</th>
                                <td>{{ $bill->meter->provider_name ?? 'House Owner' }}</td>
                            </tr>
                            <tr>
                                <th>Cheque / Favour Name</th>
                                <td>{{ $bill->cheque_name }}</td>
                            </tr>
                            <tr>
                                <th>Payment Mode</th>
                                <td><span class="badge bg-secondary">{{ $bill->payment_mode }}</span></td>
                            </tr>
                            <tr>
                                <th>Account Details</th>
                                <td>{{ $bill->payment_account_details ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Base Bill Amount</th>
                                <td class="fw-semibold">৳ {{ number_format($bill->net_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>VAT / Late Fee / Surcharge</th>
                                <td class="fw-semibold text-warning">৳ {{ number_format($bill->vat_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Payable Amount</th>
                                <td class="fw-bold text-primary">৳ {{ number_format($bill->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Last Month Bill</th>
                                <td class="fw-semibold">{{ $previousBill ? '৳ ' . number_format($previousBill->total_amount, 2) . ' (' . $previousBill->billing_month . ')' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-{{ $bill->status_badge }}">{{ $bill->status_label }}</span></td>
                            </tr>
                            @if($bill->status === 'paid')
                            <tr>
                                <th>Payment Ref / TrxID</th>
                                <td class="fw-bold text-success">{{ $bill->payment_reference }} ({{ $bill->payment_date ? $bill->payment_date->format('d M Y') : '' }})</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Postpaid / Prepaid Calculation Breakdown -->
            @if($bill->bill_type === 'postpaid')
            <h4 class="fw-light mt-2 mb-3">Consumption & Cost Breakdown</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Previous Reading</th>
                            <th>Current Reading</th>
                            <th>Units Consumed (kWh)</th>
                            <th>Rate per Unit (৳)</th>
                            <th>Base Bill Amount (৳)</th>
                            <th>VAT / Late Fee (৳)</th>
                            <th>Total Payable Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($bill->previous_reading, 2) }}</td>
                            <td>{{ number_format($bill->current_reading, 2) }}</td>
                            <td class="fw-bold text-info">{{ number_format($bill->units_consumed, 2) }}</td>
                            <td>৳ {{ number_format($bill->rate_per_unit, 2) }}</td>
                            <td>৳ {{ number_format($bill->net_amount, 2) }}</td>
                            <td class="text-warning fw-semibold">৳ {{ number_format($bill->vat_amount, 2) }}</td>
                            <td class="fw-bold text-primary fs-6">৳ {{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @else
            <h4 class="fw-light mt-2 mb-3">Billing Cost Breakdown</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Base Recharge / Bill Amount (৳)</th>
                            <th>VAT / Service Charge / Late Fee (৳)</th>
                            <th>Total Payable Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>৳ {{ number_format($bill->net_amount, 2) }}</td>
                            <td class="text-warning fw-semibold">৳ {{ number_format($bill->vat_amount, 2) }}</td>
                            <td class="fw-bold text-primary fs-6">৳ {{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Amount in Words & Remarks -->
            <div class="alert alert-secondary fs-sm mb-4">
                <strong>Amount in Words:</strong> {{ $bill->amount_in_words }}
                @if($bill->remarks)
                    <div class="mt-1"><strong>Remarks:</strong> {{ $bill->remarks }}</div>
                @endif
            </div>

            <!-- Attachment File -->
            @if($bill->bill_file_path)
            <div class="mb-4">
                <h4 class="fw-light mb-3">Uploaded Attachment</h4>
                <a href="{{ asset('storage/' . $bill->bill_file_path) }}" target="_blank" class="btn btn-alt-primary btn-sm">
                    <i class="fa fa-download me-1"></i> View Uploaded Bill Document
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Mark as Paid -->
<div class="modal fade" id="modal-mark-paid" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('electricity.bills.pay', $bill->id) }}" method="POST">
                @csrf
                <div class="block block-rounded block-transparent mb-0">
                    <div class="block-header block-header-default bg-success text-white">
                        <h3 class="block-title text-white">Record Payment</h3>
                        <button type="button" class="btn-block-option text-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                    <div class="block-content fs-sm">
                        <div class="alert alert-info py-2 mb-3">
                            <strong>{{ $bill->requisition_no }}</strong> — Total: <strong>৳ {{ number_format($bill->total_amount, 2) }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="payment_date">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="payment_reference">BEFTN Transaction Ref / Cheque No / bKash TrxID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="e.g. BEFTN-98214 or CHQ-00129" required>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-end bg-body-light">
                        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check me-1"></i> Mark Paid</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
