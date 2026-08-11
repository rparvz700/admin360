@php
    $firstBill = $bills->first();
    $projectName = $firstBill ? $firstBill->project_name : 'N/A';
    $paymentMode = $firstBill ? $firstBill->payment_mode : 'BEFTN';
    $billType = $firstBill ? $firstBill->bill_type : 'postpaid';
    
    $grandTotal = $bills->sum('total_amount');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Bill Summary - {{ $projectName }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 8mm 12mm 8mm;
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

        .header-container {
            width: 100%;
            margin-bottom: 15px;
            position: relative;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo-section {
            width: 80px;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #1e3a8a;
            display: inline-block;
        }

        .title-section {
            text-align: center;
        }

        .title-section h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .title-section p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #333;
        }

        .title-section .doc-title {
            margin-top: 5px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            text-transform: uppercase;
        }

        .meta-info {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-info td {
            padding: 1px 0;
            border: none;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .bill-table th,
        .bill-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            vertical-align: middle;
        }

        .bill-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 8.5px;
            text-align: center;
            text-transform: uppercase;
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
            font-size: 10px;
            font-weight: bold;
            margin: 10px 0 20px 0;
            padding: 5px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .footer-signatures {
            margin-top: 80px;
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

    <!-- Header Block -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    <img src="{{ asset('media/photos/scomm_logo.png') }}" alt="Summit Logo" style="height: 35px; width: auto; object-fit: contain;">
                </td>
                <td class="title-section">
                    <h1>Summit Communications Limited</h1>
                    <p>18, Karwan Bazar Commercial Area Dhaka-1205, Bangladesh.</p>
                    <div style="font-size: 10px; font-weight: bold; margin-top: 3px;">Project Name : {{ $projectName }}</div>
                    <div class="doc-title">Electricity Bill Summary ({{ $paymentMode }})</div>
                </td>
                <td style="width: 80px; text-align: right;">
                    &nbsp;
                </td>
            </tr>
        </table>
    </div>
    @if ($billType === 'postpaid')
        <!-- Postpaid Consolidated Table -->
        <table class="bill-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">S/L</th>
                    <th rowspan="2" style="width: 12%;">Office/POP Code & Name</th>
                    <th rowspan="2" style="width: 8%;">Maintain From</th>
                    <th rowspan="2" style="width: 10%;">Land owner Name</th>
                    <th rowspan="2" style="width: 8%;">Line Owner Name</th>
                    <th rowspan="2" style="width: 5%;">Month Name</th>
                    <th colspan="2">Consumed (kWh)</th>
                    <th rowspan="2" style="width: 6.5%;">Total Elec. Bill</th>
                    <th rowspan="2" style="width: 5.5%;">service charge</th>
                    <th rowspan="2" style="width: 5%;">Vat</th>
                    <th rowspan="2" style="width: 7%;">Total Bill Amount</th>
                    <th rowspan="2" style="width: 6%;">Bill Received Date</th>
                    <th rowspan="2" style="width: 6%;">Last Payment Date</th>
                    <th rowspan="2" style="width: 6%;">Last M. Bill</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th style="width: 5.5%;">Total Cons.</th>
                    <th style="width: 4%;">Unit Price</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $sl = 1; 
                    $totalCons = 0;
                    $totalElec = 0;
                    $totalService = 0;
                    $totalVat = 0;
                    $totalAmount = 0;
                @endphp
                @foreach ($bills as $bill)
                    @php
                        // Land owner name resolution
                        $landOwnerName = null;
                        if ($bill->building_id) {
                            $agreement = \App\Models\Agreement::whereHas('floors', function ($q) use ($bill) {
                                $q->where('building_id', $bill->building_id);
                            })->with('vendor')->latest()->first();

                            if ($agreement && $agreement->vendor) {
                                $landOwnerName = $agreement->vendor->name;
                            }
                        }
                        if (!$landOwnerName) {
                            $landOwnerName = $bill->meter->vendor->name ?? $bill->meter->meter_owner ?? 'N/A';
                        }
                        
                        // Previous bill amount
                        $prevBill = \App\Models\ElectricityBill::where('meter_id', $bill->meter_id)
                            ->where('id', '<', $bill->id)
                            ->latest('id')
                            ->first();
                            
                        // Totals calculations
                        $units = (float)($bill->units_consumed + ($bill->units_peak_consumed ?? 0));
                        $totalCons += $units;
                        $totalElec += $bill->net_amount;
                        $totalService += $bill->meter_charge;
                        $totalVat += $bill->vat_amount;
                        $totalAmount += $bill->total_amount;
                    @endphp
                    <tr>
                        <td class="text-center" rowspan="2">{{ $sl++ }}</td>
                        <td rowspan="2">
                            <strong>{{ $bill->building->site_name ?? 'N/A' }}</strong>
                            @if($bill->building && ($bill->building->code ?? $bill->building->site_code))
                                <br><span style="font-size: 7.5px; color: #555;">({{ $bill->building->code ?? $bill->building->site_code }})</span>
                            @endif
                        </td>
                        <td rowspan="2">{{ $bill->meter->provider_name ?? 'House Owner' }}</td>
                        <td rowspan="2">{{ $landOwnerName }}</td>
                        <td class="text-center" rowspan="2">Building Owner</td>
                        <td class="text-center" rowspan="2">{{ $bill->billing_month }}</td>
                        <td class="text-right">{{ number_format($bill->units_consumed, 2) }} <span style="font-size: 7px; color: #666;">(Off-Peak)</span></td>
                        <td class="text-right" rowspan="2">{{ number_format($bill->rate_per_unit, 2) }}</td>
                        <td class="text-right" rowspan="2">{{ number_format($bill->net_amount, 2) }}</td>
                        <td class="text-right" rowspan="2">{{ number_format($bill->meter_charge, 2) }}</td>
                        <td class="text-right" rowspan="2">{{ number_format($bill->vat_amount, 2) }}</td>
                        <td class="text-right fw-bold" rowspan="2">{{ number_format($bill->total_amount, 2) }}</td>
                        <td class="text-center" rowspan="2">{{ $bill->created_at->format('d-m-y') }}</td>
                        <td class="text-center" rowspan="2">{{ $bill->last_payment_date ? $bill->last_payment_date->format('d-m-y') : ($bill->payment_date ? $bill->payment_date->format('d-m-y') : 'N/A') }}</td>
                        <td class="text-right" rowspan="2">{{ $prevBill ? number_format($prevBill->total_amount, 2) : 'N/A' }}</td>
                        <td rowspan="2">{{ $bill->remarks ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-right" style="border-top: none;">{{ number_format($bill->units_peak_consumed ?? 0, 2) }} <span style="font-size: 7px; color: #666;">(Peak)</span></td>
                    </tr>
                @endforeach
                <tr class="fw-bold" style="background-color: #f3f4f6;">
                    <td colspan="6" class="text-right">Total (BDT):</td>
                    <td class="text-right">{{ number_format($totalCons, 2) }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalElec, 2) }}</td>
                    <td class="text-right">{{ number_format($totalService, 2) }}</td>
                    <td class="text-right">{{ number_format($totalVat, 2) }}</td>
                    <td class="text-right" style="background-color: #e5e7eb;">{{ number_format($totalAmount, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    @else
        <!-- Prepaid Consolidated Table -->
        <table class="bill-table">
            <thead>
                <tr>
                    <th style="width: 3%;">S/L</th>
                    <th style="width: 15%;">Office/POP Code & Name</th>
                    <th style="width: 10%;">Maintain From</th>
                    <th style="width: 12%;">Land owner Name</th>
                    <th style="width: 6%;">Month Name</th>
                    <th style="width: 8%;" class="text-right">Last Balance</th>
                    <th style="width: 8%;" class="text-right">Recharge Amount</th>
                    <th style="width: 8%;" class="text-right">Per Day Cons.</th>
                    <th style="width: 8%;" class="text-right">Total Bill Amount</th>
                    <th style="width: 7%;">Bill Received Date</th>
                    <th style="width: 7%;">Last Payment Date</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $sl = 1; 
                    $totalRecharge = 0;
                    $totalAmount = 0;
                @endphp
                @foreach ($bills as $bill)
                    @php
                        // Land owner name resolution
                        $landOwnerName = null;
                        if ($bill->building_id) {
                            $agreement = \App\Models\Agreement::whereHas('floors', function ($q) use ($bill) {
                                $q->where('building_id', $bill->building_id);
                            })->with('vendor')->latest()->first();

                            if ($agreement && $agreement->vendor) {
                                $landOwnerName = $agreement->vendor->name;
                            }
                        }
                        if (!$landOwnerName) {
                            $landOwnerName = $bill->meter->vendor->name ?? $bill->meter->meter_owner ?? 'N/A';
                        }
                        
                        // Totals calculations
                        $totalRecharge += $bill->recharge_amount;
                        $totalAmount += $bill->total_amount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td>
                            <strong>{{ $bill->building->site_name ?? 'N/A' }}</strong>
                            @if($bill->building && ($bill->building->code ?? $bill->building->site_code))
                                <br><span style="font-size: 7.5px; color: #555;">({{ $bill->building->code ?? $bill->building->site_code }})</span>
                            @endif
                        </td>
                        <td>{{ $bill->meter->provider_name ?? 'House Owner' }}</td>
                        <td>{{ $landOwnerName }}</td>
                        <td class="text-center">{{ $bill->billing_month }}</td>
                        <td class="text-right">{{ number_format($bill->last_balance, 2) }}</td>
                        <td class="text-right">{{ number_format($bill->recharge_amount, 2) }}</td>
                        <td class="text-right">{{ number_format($bill->per_day_consumption, 2) }}/day</td>
                        <td class="text-right fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
                        <td class="text-center">{{ $bill->created_at->format('d-m-y') }}</td>
                        <td class="text-center">{{ $bill->last_payment_date ? $bill->last_payment_date->format('d-m-y') : ($bill->payment_date ? $bill->payment_date->format('d-m-y') : 'N/A') }}</td>
                        <td>{{ $bill->remarks ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold" style="background-color: #f3f4f6;">
                    <td colspan="6" class="text-right">Total (BDT):</td>
                    <td class="text-right">{{ number_format($totalRecharge, 2) }}</td>
                    <td></td>
                    <td class="text-right" style="background-color: #e5e7eb;">{{ number_format($totalAmount, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    @endif

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
        Amount In Words : {{ getTakaInWords($grandTotal) }}
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
                        <strong>Acknowledged By</strong><br><br>
                        Name: ____________________<br>
                        Designation: _______________<br>
                        Dept.: ____________________
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="text-align: left; display: inline-block; width: 90%;">
                        <strong>Verified By</strong><br><br>
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
                        <strong>Approved By</strong><br><br>
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
