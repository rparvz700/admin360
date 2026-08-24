<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Bill Sheet - {{ $bill->requisition_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Arial, 'Helvetica Neue', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            background: #f8fafc;
        }
        .no-print-bar {
            max-width: 960px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
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
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-back:hover {
            background-color: #334155;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px 30px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .meta-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 11px;
            border: 1px solid #e2e8f0;
            word-wrap: break-word;
        }
        .meta-table td.label {
            font-weight: bold;
            background-color: #f8fafc;
            color: #334155;
        }
        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            word-wrap: break-word;
        }
        .bill-table th, .bill-table td {
            border: 1px solid #333;
            padding: 5px 3px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .bill-table th {
            background-color: #f1f5f9;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            color: #0f172a;
        }
        .bill-table td {
            font-size: 9px;
            line-height: 1.2;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .fw-semibold { font-weight: 600; }
        
        .words-box {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            background: #f8fafc;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .signature-table td {
            width: 20%;
            text-align: center;
            vertical-align: bottom;
            height: 60px;
            padding: 0 4px;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 4px;
            font-weight: bold;
            font-size: 9.5px;
        }
        .signature-sub {
            font-size: 8.5px;
            color: #555;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Top Action Bar (Hidden during print) -->
<div class="no-print-bar">
    <div>
        <strong>Electricity Requisition Sheet #{{ $bill->requisition_no }}</strong>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('electricity.bills.show', $bill->id) }}" class="btn-back">← Back to Bill Details</a>
        <button onclick="window.print();" class="btn-print">
            🖨️ Print / Save PDF
        </button>
    </div>
</div>

<div class="container">
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: fixed;">
            <tr>
                <td style="width: 25%; text-align: left; vertical-align: middle;">
                    <img src="{{ asset('media/photos/scomm_logo.png') }}" alt="Summit Communications Limited" style="max-height: 50px; width: auto;">
                </td>
                <td style="width: 50%; text-align: center; vertical-align: middle;">
                    <h2 style="margin: 0; font-size: 16px; text-transform: uppercase; font-weight: bold;">Electricity Bill - {{ $bill->payment_mode }}</h2>
                    <h4 style="margin: 4px 0 0 0; font-size: 12px; font-weight: normal; color: #333;">Facilities & Infrastructure Operations</h4>
                </td>
                <td style="width: 25%;"></td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 35%;">
            <col style="width: 15%;">
            <col style="width: 35%;">
        </colgroup>
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
        @if($bill->bill_type === 'postpaid')
        <colgroup>
            <col style="width: 3.5%;">
            <col style="width: 15.5%;">
            <col style="width: 9%;">
            <col style="width: 7%;">
            <col style="width: 9.5%;">
            <col style="width: 6.5%;">
            <col style="width: 6.5%;">
            <col style="width: 6.5%;">
            <col style="width: 5.5%;">
            <col style="width: 7.5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 9.5%;">
        </colgroup>
        @else
        <colgroup>
            <col style="width: 3.5%;">
            <col style="width: 17.5%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 8.5%;">
            <col style="width: 8.5%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 5.5%;">
            <col style="width: 5.5%;">
            <col style="width: 5.5%;">
            <col style="width: 5.5%;">
            <col style="width: 9.5%;">
        </colgroup>
        @endif
        <thead>
            <tr>
                <th class="text-center">SL</th>
                <th>Site / Office / POP Name</th>
                <th>Meter No</th>
                <th>Bill Month</th>
                @if($bill->bill_type === 'postpaid')
                    <th>Category</th>
                    <th class="text-right">Prev Reading</th>
                    <th class="text-right">Pres Reading</th>
                    <th class="text-right">Consumed Unit</th>
                    <th class="text-right">Unit Price</th>
                @else
                    <th class="text-right">Last Balance</th>
                    <th class="text-right">Recharge Amt</th>
                    <th class="text-right">Per Day Cons.</th>
                @endif
                <th class="text-right">Base Bill</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Late Fee</th>
                <th class="text-right">Meter Chg</th>
                <th class="text-right">Others</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($bill->bill_type === 'postpaid')
            <tr>
                <td class="text-center" rowspan="2">1</td>
                <td rowspan="2">
                    <strong>{{ $bill->building->site_name ?? 'N/A' }}</strong>
                    @if($bill->building && ($bill->building->code ?? $bill->building->site_code))
                        <br><span style="font-size: 8px; color: #555;">({{ $bill->building->code ?? $bill->building->site_code }})</span>
                    @endif
                </td>
                <td rowspan="2">{{ $bill->meter->meter_number ?? 'N/A' }}</td>
                <td rowspan="2" class="text-center">{{ $bill->billing_month }}</td>
                <td class="fw-semibold">Off-Peak (Flat)</td>
                <td class="text-right">{{ number_format($bill->previous_reading, 2) }}</td>
                <td class="text-right">{{ number_format($bill->current_reading, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->units_consumed, 2) }}</td>
                <td class="text-right">{{ number_format($bill->rate_per_unit, 2) }}</td>
                <td class="text-right" rowspan="2">{{ number_format($bill->net_amount, 2) }}</td>
                <td class="text-right" rowspan="2">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="text-right" rowspan="2">{{ number_format($bill->late_fee, 2) }}</td>
                <td class="text-right" rowspan="2">{{ number_format($bill->meter_charge, 2) }}</td>
                <td class="text-right" rowspan="2">{{ number_format($bill->others_amount, 2) }}</td>
                <td class="text-right fw-bold" rowspan="2">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="fw-semibold">Peak</td>
                <td class="text-right">{{ number_format($bill->previous_peak_reading ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($bill->current_peak_reading ?? 0, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->units_peak_consumed ?? 0, 2) }}</td>
                <td class="text-right" style="border-top: none;">{{ number_format($bill->rate_peak_per_unit ?? 0, 2) }}</td>
            </tr>
            @else
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong>{{ $bill->building->site_name ?? 'N/A' }}</strong>
                    @if($bill->building && ($bill->building->code ?? $bill->building->site_code))
                        <br><span style="font-size: 8px; color: #555;">({{ $bill->building->code ?? $bill->building->site_code }})</span>
                    @endif
                </td>
                <td>{{ $bill->meter->meter_number ?? 'N/A' }}</td>
                <td class="text-center">{{ $bill->billing_month }}</td>
                <td class="text-right">{{ number_format($bill->last_balance, 2) }}</td>
                <td class="text-right">{{ number_format($bill->recharge_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->per_day_consumption, 2) }}/day</td>
                <td class="text-right">{{ number_format($bill->net_amount, 2) }}</td>
                <td class="text-right">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="text-right">{{ number_format($bill->late_fee, 2) }}</td>
                <td class="text-right">{{ number_format($bill->meter_charge, 2) }}</td>
                <td class="text-right">{{ number_format($bill->others_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="{{ $bill->bill_type === 'postpaid' ? 9 : 7 }}" class="text-right fw-bold">Total (BDT):</td>
                <td class="text-right fw-bold">{{ number_format($bill->net_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->late_fee, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->meter_charge, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($bill->others_amount, 2) }}</td>
                <td class="text-right fw-bold" style="background-color: #f1f5f9;">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="words-box">
        Amount in Words: {{ $bill->amount_in_words }}
        @if($bill->is_consumption_edited && $bill->consumption_edit_remarks)
            <div style="margin-top: 5px; font-weight: normal; font-size: 10px; color: #b45309;">
                <strong>* Manual Consumption Override Note:</strong> {{ $bill->consumption_edit_remarks }}
            </div>
        @endif
    </div>

    @if($bill->payment_account_details)
    <div style="margin-bottom: 20px; font-size: 10px;">
        <strong>Payment Account Info:</strong> {{ $bill->payment_account_details }}
    </div>
    @endif

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
