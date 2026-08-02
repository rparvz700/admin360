<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Electricity Bill Sheet - {{ $bill->requisition_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            background: #fff;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 25px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h4 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .meta-table td.label {
            font-weight: bold;
            width: 15%;
        }
        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .bill-table th, .bill-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .bill-table th {
            background-color: #f2f2f2;
            font-size: 12px;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .words-box {
            border: 1px solid #333;
            padding: 10px;
            background: #f9f9f9;
            font-weight: bold;
            margin-bottom: 40px;
        }

        /* Signature block matching company Excel format */
        .signature-section {
            margin-top: 60px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 20%;
            text-align: center;
            vertical-align: bottom;
            height: 70px;
            padding: 0 5px;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
            font-size: 11px;
        }
        .signature-sub {
            font-size: 10px;
            color: #666;
        }

        @media print {
            body { padding: 0; }
            .container { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; text-align: right;">
    <button onclick="window.print();" style="padding: 8px 16px; background: #0284c7; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
        🖨️ Print Electricity Bill
    </button>
</div>

<div class="container">
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="width: 25%; text-align: left; vertical-align: middle;">
                    <img src="{{ asset('media/photos/scomm_logo.png') }}" alt="Summit Communications Limited" style="max-height: 55px; width: auto;">
                </td>
                <td style="width: 50%; text-align: center; vertical-align: middle;">
                    <h2 style="margin: 0; font-size: 18px; text-transform: uppercase;">Electricity Bill - {{ $bill->payment_mode }}</h2>
                    <h4 style="margin: 5px 0 0 0; font-size: 13px; font-weight: normal;">Facilities & Infrastructure Operations</h4>
                </td>
                <td style="width: 25%;"></td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Project Name:</td>
            <td>{{ $bill->project_name }}</td>
            <td class="label">Date:</td>
            <td>{{ date('d-M-Y', strtotime($bill->created_at)) }}</td>
        </tr>
        <tr>
            <td class="label">Ref. No:</td>
            <td class="fw-bold">{{ $bill->requisition_no }}</td>
            <td class="label">Prepared By:</td>
            <td>{{ $bill->creator->name ?? 'System Admin' }}</td>
        </tr>
        <tr>
            <td class="label">Land Owner Name:</td>
            <td class="fw-bold">{{ $landOwnerName }}</td>
            <td class="label">Last Payment Date:</td>
            <td>{{ $bill->last_payment_date ? $bill->last_payment_date->format('d-M-Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Last Month Bill:</td>
            <td class="fw-bold">{{ $previousBill ? '৳ ' . number_format($previousBill->total_amount, 2) . ' (' . $previousBill->billing_month . ')' : 'N/A' }}</td>
            <td class="label">Purposes:</td>
            <td>Electricity Bill Payment ({{ ucfirst($bill->bill_type) }})</td>
        </tr>
    </table>

    <table class="bill-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">SL</th>
                <th>Site / Office / POP Name</th>
                <th style="width: 10%;">Meter No</th>
                <th style="width: 9%;">Bill Month</th>
                <th class="text-right" style="width: 9%;">Prev Reading</th>
                <th class="text-right" style="width: 9%;">Pres Reading</th>
                <th class="text-right" style="width: 9%;">Consumed Unit</th>
                <th class="text-right" style="width: 8%;">Unit Price</th>
                <th class="text-right" style="width: 10%;">Base Bill</th>
                <th class="text-right" style="width: 10%;">Late Fee/VAT</th>
                <th class="text-right" style="width: 12%;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $bill->building->site_name ?? 'N/A' }} {{ ($bill->building->code ?? $bill->building->site_code) ? "(" . ($bill->building->code ?? $bill->building->site_code) . ")" : '' }}</td>
                <td>{{ $bill->meter->meter_number ?? 'N/A' }}</td>
                <td>{{ $bill->billing_month }}</td>
                <td class="text-right">{{ $bill->bill_type === 'postpaid' ? number_format($bill->previous_reading, 2) : 'N/A' }}</td>
                <td class="text-right">{{ $bill->bill_type === 'postpaid' ? number_format($bill->current_reading, 2) : 'N/A' }}</td>
                <td class="text-right fw-bold">{{ $bill->bill_type === 'postpaid' ? number_format($bill->units_consumed, 2) : 'N/A' }}</td>
                <td class="text-right">{{ $bill->bill_type === 'postpaid' ? number_format($bill->rate_per_unit, 2) : 'N/A' }}</td>
                <td class="text-right">{{ number_format($bill->net_amount, 2) }}</td>
                <td class="text-right">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right fw-bold">Total (BDT):</td>
                <td class="text-right fw-bold">{{ number_format($bill->net_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="text-right fw-bold" style="background-color: #f2f2f2;">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="words-box">
        Amount in Words: {{ $bill->amount_in_words }}
    </div>

    @if($bill->payment_account_details || $bill->cheque_name)
    <div style="margin-bottom: 20px; font-size: 11px;">
        <strong>Cheque / Favour Name:</strong> {{ $bill->cheque_name }} | 
        <strong>Payment Account Info:</strong> {{ $bill->payment_account_details ?? 'N/A' }}
    </div>
    @endif

    <!-- Official Signature Block matching company Excel sheets -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Prepared By</div>
                    <div class="signature-sub">{{ $bill->creator->name ?? 'Executive' }}</div>
                    <div class="signature-sub">Administration</div>
                </td>
                <td>
                    <div class="signature-line">Checked & Verified By</div>
                    <div class="signature-sub">Central Power (CP)</div>
                </td>
                <td>
                    <div class="signature-line">Checked By</div>
                    <div class="signature-sub">Deputy Manager</div>
                    <div class="signature-sub">Administration</div>
                </td>
                <td>
                    <div class="signature-line">Recommended By</div>
                    <div class="signature-sub">Senior Manager</div>
                    <div class="signature-sub">Administration</div>
                </td>
                <td>
                    <div class="signature-line">Approved By</div>
                    <div class="signature-sub">Chief Corporate Affairs Officer / Head</div>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
