<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Requisition Sheet - {{ $bill->requisition_no }}</title>
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
        🖨️ Print Requisition Sheet
    </button>
</div>

<div class="container">
    <div class="header">
        <h2>Requisition for Electricity Bill - {{ $bill->payment_mode }}</h2>
        <h4>Facilities & Infrastructure Operations</h4>
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
            <td class="label">HR ID:</td>
            <td>{{ $bill->creator->id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Purposes:</td>
            <td>Electricity Bill Payment ({{ ucfirst($bill->bill_type) }})</td>
            <td class="label">Requisitioner:</td>
            <td>{{ $bill->creator->name ?? 'System Admin' }}</td>
        </tr>
    </table>

    <table class="bill-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">SL No</th>
                <th style="width: 15%;">Site Code</th>
                <th>Site / Office / POP Name</th>
                <th style="width: 10%;">Project</th>
                <th style="width: 10%;">Bill Month</th>
                <th style="width: 15%;">Last Payment Date</th>
                <th class="text-right" style="width: 15%;">Bill Amount (BDT)</th>
                <th style="width: 15%;">Payment Mode</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $bill->building->code ?? $bill->building->site_code ?? 'N/A' }}</td>
                <td>{{ $bill->building->site_name ?? 'N/A' }}</td>
                <td>{{ $bill->project_name }}</td>
                <td>{{ $bill->billing_month }}</td>
                <td>{{ $bill->last_payment_date ? $bill->last_payment_date->format('d-M-Y') : 'N/A' }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
                <td>{{ $bill->payment_mode }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right fw-bold">Total (BDT):</td>
                <td class="text-right fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
                <td></td>
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
                    <div class="signature-line">Requisition By</div>
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
