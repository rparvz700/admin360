@php
    $firstBill = $bills->first();
    $projectName = $firstBill ? $firstBill->project_name : 'N/A';
    $paymentMode = $firstBill ? $firstBill->payment_mode : 'BEFTN';
    
    $grandTotal = $bills->sum('total_amount');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepaid Meter Recharge Requisition - {{ $projectName }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .no-print {
            text-align: right;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f3f4f6;
            border-radius: 4px;
        }

        .no-print button {
            padding: 6px 14px;
            font-size: 11px;
            cursor: pointer;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .no-print button:hover {
            background: #1d4ed8;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .header-table td {
            vertical-align: top;
            border: none;
        }

        .meta-box-table {
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 8px;
            float: right;
            width: 280px;
        }

        .meta-box-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            line-height: 1.2;
        }

        .doc-title-main {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin: 10px 0 8px 0;
            clear: both;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .bill-table th,
        .bill-table td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }

        .bill-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            line-height: 1.2;
        }

        .bill-table td {
            font-size: 8px;
            line-height: 1.15;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .amount-words-section {
            font-size: 9.5px;
            font-weight: bold;
            margin: 8px 0 15px 0;
            padding: 4px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .footer-signatures {
            margin-top: 60px;
            width: 100%;
            page-break-inside: avoid;
        }

        .footer-signatures table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-signatures td {
            width: 20%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 5px;
            border: none;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 8.5px;
            line-height: 1.3;
        }

        .sig-name {
            font-weight: bold;
            color: #000;
        }

        .sig-title {
            color: #333;
        }

        .sig-dept {
            color: #555;
            font-style: italic;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print();">Print Summary Report</button>
    </div>

    <!-- Header Block: Logo on Left, Title in Middle -->
    <table class="header-table" style="width: 100%; margin-bottom: 12px;">
        <tr>
            <td style="width: 25%; vertical-align: middle;">
                <img src="{{ asset('media/photos/scomm_logo.png') }}" alt="Summit Logo" style="height: 50px; width: auto; object-fit: contain; display: block;">
            </td>
            <td style="width: 75%; text-align: center; vertical-align: middle; padding-right: 15%;">
                <h1 style="font-size: 14px; font-weight: bold; color: #000; margin: 0;">
                    Requisition for Prepaid Meter Recharge for the month of {{ $firstBill ? $firstBill->billing_month : 'N/A' }}
                </h1>
            </td>
        </tr>
    </table>

    <!-- Left-aligned Requisition Meta Details Block -->
    <div style="margin-bottom: 12px;">
        <table class="meta-box-table" style="border-collapse: collapse; border: 1px solid #000; font-size: 8.5px; width: 320px; float: left;">
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 35%;">Project:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">{{ $projectName }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">Ref. No:</td>
                <td style="border: 1px solid #000; padding: 2px 6px; font-size: 7.5px;">{{ $firstBill->requisition_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">Purposes:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">Prepaid meter recharge for the month of {{ $firstBill ? $firstBill->billing_month : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">Date:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">{{ $firstBill && $firstBill->created_at ? $firstBill->created_at->format('d-M-y') : date('d-M-y') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">HR ID:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">{{ ($firstBill && $firstBill->creator && $firstBill->creator->hr_id) ? $firstBill->creator->hr_id : '' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">Name:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">{{ $firstBill->creator->name ?? '' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; font-weight: bold;">Designation:</td>
                <td style="border: 1px solid #000; padding: 2px 6px;">{{ $firstBill->creator->designation ?? '' }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td style="border: 1px solid #000; padding: 2px 6px;">Total ( BDT)</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ number_format($grandTotal, 0) }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <!-- Prepaid Consolidated Table -->
    <table class="bill-table">
        <colgroup>
            <col style="width: 2.5%;">
            <col style="width: 15%;">
            <col style="width: 8%;">
            <col style="width: 6%;">
            <col style="width: 5.5%;">
            <col style="width: 7%;">
            <col style="width: 9%;">
            <col style="width: 7%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 9%;">
            <col style="width: 7%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">SL No.</th>
                <th rowspan="2">Site/Office/POP Name</th>
                <th rowspan="2">Location / District</th>
                <th rowspan="2">Site Code</th>
                <th rowspan="2">PoP Type</th>
                <th rowspan="2">Power Authority</th>
                <th colspan="7">Description of Bill</th>
            </tr>
            <tr>
                <th>Prepaid Meter No.</th>
                <th>Last Recharge date</th>
                <th class="text-right">Last Recharged Amount (BDT)</th>
                <th class="text-right">Total Balance After last Recharge</th>
                <th class="text-right">Current Balance {{ $firstBill && $firstBill->recharge_date ? $firstBill->recharge_date->format('d-m-y') : date('d-m-y') }} (Approx)</th>
                <th class="text-right">Amount To Be Recharged (BDT)</th>
                <th class="text-right">Per Day Expense (Approx.)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sl = 1; 
                $totalLastRecharge = 0;
                $totalBalanceAfter = 0;
                $totalCurrentBalance = 0;
                $totalRecharge = 0;
            @endphp
            @foreach ($bills as $bill)
                @php
                    // Location / District
                    $districtName = $bill->building->district ?? 'N/A';
                    
                    // POP Type from floor premises_type
                    $popType = null;
                    if ($bill->meter && $bill->meter->floor) {
                        $popType = $bill->meter->floor->premises_type;
                    }
                    if (!$popType && $bill->building) {
                        $firstFloor = $bill->building->floors->first();
                        if ($firstFloor) {
                            $popType = $firstFloor->premises_type;
                        }
                    }
                    if (!$popType) {
                        $popType = 'N/A';
                    }
                    
                    // Totals calculations
                    $totalLastRecharge += (float)$bill->last_recharge_amount;
                    $totalBalanceAfter += (float)$bill->balance_after_last_recharge;
                    $totalCurrentBalance += (float)$bill->last_balance;
                    $totalRecharge += (float)$bill->recharge_amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $sl++ }}</td>
                    <td>
                        <strong>{{ $bill->building->site_name ?? 'N/A' }}</strong>
                    </td>
                    <td>{{ $districtName }}</td>
                    <td class="text-center">{{ $bill->building->code ?? ($bill->building->site_code ?? 'N/A') }}</td>
                    <td class="text-center">{{ $popType }}</td>
                    <td class="text-center">{{ $bill->meter->authority_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $bill->meter->meter_number }}</td>
                    <td class="text-center">{{ $bill->last_recharge_date ? $bill->last_recharge_date->format('d-M-y') : 'N/A' }}</td>
                    <td class="text-right">{{ number_format($bill->last_recharge_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($bill->balance_after_last_recharge, 2) }}</td>
                    <td class="text-right">{{ number_format($bill->last_balance, 2) }}</td>
                    <td class="text-right fw-bold">{{ number_format($bill->recharge_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($bill->per_day_consumption, 2) }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold" style="background-color: #f3f4f6;">
                <td colspan="11" class="text-right">Total ( BDT)</td>
                <td class="text-right" style="background-color: #e5e7eb;">{{ number_format($totalRecharge, 0) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Amount in Words -->
    @php
        if (!function_exists('getTakaInWords')) {
            function getTakaInWords($amount) {
                $amount = (float)$amount;
                $no = (int)floor($amount);
                $point = (int)round(($amount - $no) * 100);
                
                $words = array(
                    0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
                    30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
                    80 => 'Eighty', 90 => 'Ninety'
                );
                
                $levels = array(
                    array(10000000, 'Crore'),
                    array(100000, 'Lac'),
                    array(1000, 'Thousand'),
                    array(100, 'Hundred')
                );
                
                $result = '';
                $temp = $no;
                
                foreach ($levels as $level) {
                    $divisor = $level[0];
                    $name = $level[1];
                    if ($temp >= $divisor) {
                        $qty = (int)floor($temp / $divisor);
                        $temp = $temp % $divisor;
                        $result .= convertBelowHundred($qty, $words) . ' ' . $name . ' ';
                    }
                }
                
                if ($temp > 0) {
                    if ($result != '') {
                        $result .= 'and ';
                    }
                    $result .= convertBelowHundred($temp, $words) . ' ';
                }
                
                $result = trim($result);
                if ($result == '') {
                    $result = 'Zero';
                }
                
                $takaStr = 'Taka ' . $result;
                
                if ($point > 0) {
                    $takaStr .= ' and ' . convertBelowHundred($point, $words) . ' Paisa';
                }
                
                return $takaStr . ' Only';
            }
        }

        if (!function_exists('convertBelowHundred')) {
            function convertBelowHundred($num, $words) {
                if ($num < 21) {
                    return $words[$num];
                }
                $tens = (int)floor($num / 10) * 10;
                $ones = $num % 10;
                return $words[$tens] . ($ones > 0 ? ' ' . $words[$ones] : '');
            }
        }
    @endphp
    
    <div class="amount-words-section">
        Amount in words: {{ getTakaInWords($grandTotal) }}
    </div>

    <!-- Signatures Section -->
    <div class="footer-signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Requisition By</strong><br><br>
                        Name: ____________________<br>
                        Designation: _______________<br>
                        Dept.: ____________________
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Checked & Verified By</strong><br><br>
                        Name: ____________________<br>
                        Designation: _______________<br>
                        Dept.: ____________________
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Checked By</strong><br><br>
                        Name: ____________________<br>
                        Designation: _______________<br>
                        Dept.: ____________________
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Recommended By</strong><br><br>
                        Name: ____________________<br>
                        Designation: _______________<br>
                        Dept.: ____________________
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Approve By</strong><br><br>
                        <span class="sig-name">Brig. Gen. Ali Mortoza Khan (Retd.)</span><br>
                        <span class="sig-title">Chief Corporate Affairs Officer</span><br>
                        <span class="sig-dept">&nbsp;</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
