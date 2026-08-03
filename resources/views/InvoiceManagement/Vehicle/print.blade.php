<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle_Invoice_{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }

        .page-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }

        /* Action Toolbar (Hidden during print) */
        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #0369a1;
        }

        .btn-back {
            background-color: #475569;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #334155;
        }

        /* Header Layout */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }

        .company-sub {
            font-size: 11px;
            font-weight: 600;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .company-meta {
            font-size: 11px;
            color: #64748b;
        }

        .doc-title-box {
            text-align: right;
        }

        .doc-badge {
            display: inline-block;
            background: #0284c7;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .inv-number {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 8px;
        }

        /* Details Grid */
        .info-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 12px 15px;
        }

        .info-box-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #0284c7;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11.5px;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #0f172a;
            font-weight: 600;
        }

        /* Status Badges */
        .badge-status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-partial {
            background: #fef9c3;
            color: #a16207;
            border: 1px solid #fef08a;
        }

        .badge-pending {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-overdue {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Tables */
        .section-heading {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            margin: 20px 0 8px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .table-custom th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        .table-custom td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            color: #1e293b;
        }

        .table-custom tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: 700;
        }

        /* Financial Summary Box */
        .financial-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        .words-box {
            flex: 1.2;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 12px 15px;
            border-radius: 5px;
        }

        .summary-box {
            flex: 0.8;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            overflow: hidden;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }

        .summary-row.total-row {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            font-size: 12.5px;
            border-bottom: none;
        }

        /* Signatures */
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sig-box {
            text-align: center;
            width: 30%;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            margin-bottom: 4px;
        }

        .sig-title {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .page-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Top Action Bar (Hidden during print / PDF export) -->
    <div class="no-print-bar">
        <div>
            <strong>Vehicle Invoice #{{ $invoice->invoice_number }}</strong>
            <span style="opacity: 0.7; margin-left: 10px; font-size: 12px;">Vehicle Maintenance ERP Document</span>
        </div>
        <div>
            <a href="{{ route('invoices.vehicle.show', $invoice->id) }}" class="btn-back">← Back to Invoice</a>
            <button onclick="window.print();" class="btn-print">
                🖨 Save / Download PDF
            </button>
        </div>
    </div>

    <div class="page-container">
        <!-- Header -->
        <div class="invoice-header">
            <div>
                <img src="{{ asset('media/photos/scomm_logo.png') }}" alt="Summit Communications Limited"
                    style="max-height: 60px; width: auto;">
            </div>
            <div class="doc-title-box">
                <div class="doc-badge">VEHICLE MAINTENANCE INVOICE</div>
                <div class="inv-number">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Meta Info Grid -->
        <div class="info-grid">
            <!-- Vendor / Garage Info -->
            <div class="info-box">
                <div class="info-box-title">Garage / Vendor / Service Provider</div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                    {{ $invoice->vendor->name ?? 'N/A' }}
                </div>
                <div class="info-row">
                    <span class="info-label">Vendor Code:</span>
                    <span class="info-value">{{ $invoice->vendor->vendor_code ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $invoice->vendor->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $invoice->vendor->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $invoice->vendor->address ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="info-box">
                <div class="info-box-title">Invoice Details</div>
                <div class="info-row">
                    <span class="info-label">Invoice Date:</span>
                    <span class="info-value">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Due Date:</span>
                    <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value">
                        <span class="badge-status badge-{{ $invoice->getPaymentStatusBadge() }}">
                            {{ strtoupper($invoice->getPaymentStatusLabel()) }}
                        </span>
                    </span>
                </div>
                @if ($invoice->payment_method)
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</span>
                    </div>
                @endif
                @if ($invoice->payment_date)
                    <div class="info-row">
                        <span class="info-label">Payment Date:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($invoice->payment_date)->format('d M Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Primary Billing Particulars -->
        <div class="section-heading">Billing Particulars</div>
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 8%;">SL</th>
                    <th>Particulars / Maintenance Service Summary</th>
                    <th class="text-right" style="width: 18%;">Subtotal (৳)</th>
                    <th class="text-right" style="width: 18%;">Tax Amount (৳)</th>
                    <th class="text-right" style="width: 18%;">Discount (৳)</th>
                    <th class="text-right" style="width: 20%;">Total (৳)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        {!! $invoice->invoice_item_html !!}
                        @if ($invoice->remarks)
                            <div style="font-size: 10.5px; color: #64748b; margin-top: 4px;"><strong>Remarks:</strong> {{ $invoice->remarks }}</div>
                        @endif
                    </td>
                    <td class="text-right">৳ {{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="text-right">৳ {{ number_format($invoice->tax_amount, 2) }}</td>
                    <td class="text-right">৳ {{ number_format($invoice->discount_amount, 2) }}</td>
                    <td class="text-right fw-bold" style="color: #0284c7;">৳ {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Detailed Maintenance Record Breakdown -->
        @if ($invoice->maintenances && $invoice->maintenances->count() > 0)
            <div class="section-heading">Detailed Vehicle Maintenance Breakdown</div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 6%;">SL</th>
                        <th style="width: 22%;">Vehicle Reg #</th>
                        <th style="width: 20%;">Maintenance Type</th>
                        <th>Service Description / Work Order Summary</th>
                        <th class="text-right" style="width: 20%;">Cost (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->maintenances as $index => $maint)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $maint->vehicle->registration_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge-status badge-pending">
                                    {{ ucfirst($maint->maintenance_type ?? 'Service') }}
                                </span>
                            </td>
                            <td>{{ $maint->service_description ?? 'N/A' }}</td>
                            <td class="text-right fw-bold">৳ {{ number_format($maint->cost ?? $invoice->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Summary Grid -->
        <div class="financial-summary">
            <div class="words-box">
                <div style="font-weight: 700; font-size: 10.5px; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">
                    Amount in Words
                </div>
                <div style="font-weight: 600; font-size: 11.5px; color: #0f172a;">
                    @php
                        $fmt = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                        $words = ucfirst($fmt->format($invoice->total_amount));
                    @endphp
                    Taka {{ $words }} Only.
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>৳ {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Tax Amount:</span>
                    <span>৳ {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Discount:</span>
                    <span>- ৳ {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                <div class="summary-row total-row">
                    <span>Grand Total:</span>
                    <span>৳ {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Paid Amount:</span>
                    <span style="color: #15803d; font-weight: 600;">৳ {{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Balance Due:</span>
                    <span style="color: #b91c1c; font-weight: 700;">৳ {{ number_format($invoice->getOutstandingAmount(), 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">Vehicle In-Charge</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">Verified & Checked By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-title">Authorized Signatory</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            Computer generated document via Admin360 ERP — Summit Communications Limited. No physical signature required.
        </div>
    </div>
</body>
</html>
