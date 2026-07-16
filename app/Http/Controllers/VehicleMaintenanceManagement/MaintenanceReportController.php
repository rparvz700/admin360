<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\Vendor;
use App\Models\VehiclePart;
use App\Models\VehicleMaintenancePart;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaintenanceReportController extends Controller
{
    /**
     * Display maintenance dashboard
     */
    public function dashboard()
    {
        // Total vehicles
        $totalVehicles = Vehicle::count();

        // Vehicles due for service (next 7 days or by KM)
        // $vehiclesDue = VehicleMaintenance::where('current_service_completed', true)
        //     ->where(function ($q) {
        //         $q->where('next_service_due_date', '<=', now()->addDays(7))
        //           ->orWhereNotNull('next_service_due_km');
        //     })
            $daysAhead = 7;
            $vehiclesDue = Vehicle::whereHas('maintenances', function ($q) use ($daysAhead) {
                $q->where('current_service_completed', true)
                  ->where(function ($q2) use ($daysAhead) {
                      $q2->where('next_service_due_date', '<=', now()->addDays($daysAhead)->toDateString())
                         ->orWhereRaw('next_service_due_km <= (SELECT meter_reading FROM vehicle_operational_logs WHERE vehicle_id = vehicles.id ORDER BY logged_at DESC LIMIT 1)');
                  });
            })
            ->distinct('id')
            ->count();

        // Recent maintenance activities (last 10)
        $recentMaintenances = VehicleMaintenance::with(['vehicle', 'vendor'])
            ->orderByDesc('start_datetime')
            ->limit(10)
            ->get();

        // Monthly cost trends (last 6 months)
        $monthlyCosts = VehicleMaintenance::select(
                DB::raw("TO_CHAR(start_datetime, 'YYYY-MM') as month"),
                DB::raw('SUM(total_service_cost) as total_cost')
            )
            ->where('start_datetime', '>=', now()->subMonths(6))
            ->groupBy(DB::raw("TO_CHAR(start_datetime, 'YYYY-MM')"))
            ->orderBy('month')
            ->get();


        // Top 5 vendors by cost
        $topVendors = Vendor::select('vendors.*')
            ->leftJoin('vehicle_maintenances', 'vendors.id', '=', 'vehicle_maintenances.vendor_id')
            ->selectRaw('SUM(vehicle_maintenances.total_service_cost) as total_cost')
            ->groupBy('vendors.id')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        // High-cost vehicles (top 5)
        $highCostVehicles = Vehicle::select('vehicles.*')
            ->leftJoin('vehicle_maintenances', 'vehicles.id', '=', 'vehicle_maintenances.vehicle_id')
            ->selectRaw('SUM(vehicle_maintenances.total_service_cost) as total_cost')
            ->groupBy('vehicles.id')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        // Pending/Overdue invoices
        $pendingInvoices = Invoice::pending()->count();
        $overdueInvoices = Invoice::overdue()->count();

        return view('VehicleManagement.VehicleMaintenance.dashboard', compact(
            'totalVehicles',
            'vehiclesDue',
            'recentMaintenances',
            'monthlyCosts',
            'topVendors',
            'highCostVehicles',
            'pendingInvoices',
            'overdueInvoices'
        ));
    }

    /**
     * Vehicle-wise cost report
     */
    public function vehicleCost(Request $request)
    {
        $startDate = $request->input('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $vehicles = Vehicle::with(['maintenances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_datetime', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
            }])
            ->get()
            ->map(function ($vehicle) {
                return [
                    'vehicle' => $vehicle,
                    'total_maintenances' => $vehicle->maintenances->count(),
                    'total_cost' => $vehicle->maintenances->sum('total_service_cost'),
                    'routine_cost' => $vehicle->maintenances->where('maintenance_type', 'routine')->sum('total_service_cost'),
                    'breakdown_cost' => $vehicle->maintenances->where('maintenance_type', 'breakdown')->sum('total_service_cost'),
                    'accident_cost' => $vehicle->maintenances->where('maintenance_type', 'accident')->sum('total_service_cost'),
                ];
            })
            ->sortByDesc('total_cost');

        return view('VehicleManagement.VehicleMaintenance.Reports.vehicle-cost', compact('vehicles', 'startDate', 'endDate'));
    }

    /**
     * Vendor-wise cost report
     */
    public function vendorCost(Request $request)
    {
        $startDate = $request->input('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $vendors = Vendor::with(['maintenances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_datetime', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
            }])
            ->get()
            ->map(function ($vendor) {
                return [
                    'vendor' => $vendor,
                    'total_maintenances' => $vendor->maintenances->count(),
                    'total_cost' => $vendor->maintenances->sum('total_service_cost'),
                    'avg_cost' => $vendor->maintenances->avg('total_service_cost'),
                    'vehicles_serviced' => $vendor->maintenances->pluck('vehicle_id')->unique()->count(),
                ];
            })
            ->sortByDesc('total_cost');

        return view('VehicleManagement.VehicleMaintenance.Reports.vendor-cost', compact('vendors', 'startDate', 'endDate'));
    }

    /**
     * Monthly expenses summary
     */
    public function monthlyExpenses(Request $request)
    {
        $year = $request->input('year', now()->year);

        $expenses = VehicleMaintenance::select(
                DB::raw('EXTRACT(MONTH FROM start_datetime) as month'),
                DB::raw('COUNT(*) as maintenance_count'),
                DB::raw('SUM(total_service_cost) as total_cost'),
                DB::raw('SUM(labor_cost) as total_labor'),
                DB::raw('SUM(parts_cost) as total_parts')
            )
            ->whereYear('start_datetime', $year)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM start_datetime)'))
            ->orderBy('month')
            ->get()
            ->keyBy(function ($item) {
                return (int) $item->month; // important: cast to int
            });

        // Fill missing months with zeros
        $allMonths = collect(range(1, 12))->map(function ($month) use ($expenses) {
            return $expenses->get($month, (object)[
                'month' => $month,
                'maintenance_count' => 0,
                'total_cost' => 0,
                'total_labor' => 0,
                'total_parts' => 0,
            ]);
        });

        // Vendor-wise breakdown
        $vendorExpenses = Vendor::select('vendors.name')
            ->leftJoin('vehicle_maintenances', 'vendors.id', '=', 'vehicle_maintenances.vendor_id')
            ->selectRaw('SUM(vehicle_maintenances.total_service_cost) as total_cost')
            ->whereYear('vehicle_maintenances.start_datetime', $year)
            ->groupBy('vendors.id', 'vendors.name')
            ->orderByDesc('total_cost')
            ->get();

        return view(
            'VehicleManagement.VehicleMaintenance.Reports.monthly-expenses',
            compact('allMonths', 'vendorExpenses', 'year')
        );
    }


    /**
     * Parts replacement history report
     */
    public function partsHistory(Request $request)
    {
        $vehicleId = $request->input('vehicle_id');
        $partId = $request->input('part_id');
        $startDate = $request->input('start_date', now()->subYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Base query
        $baseQuery = VehicleMaintenancePart::with([
            'part',
            'vehicle',
            'maintenance',
            'vendor'
        ]);

        if ($vehicleId) {
            $baseQuery->where('vehicle_id', $vehicleId);
        }

        if ($partId) {
            $baseQuery->where('vehicle_part_id', $partId);
        }

        // Filter by maintenance date
        $baseQuery->whereHas('maintenance', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_datetime', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        });

        // Paginated data
        $partsHistory = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate(50);

        // Summary statistics (use cloned queries)
        $stats = [
            'total_replacements' => (clone $baseQuery)
                ->whereIn('action_type', ['replace', 'replace_brand_new', 'replace_recondition'])
                ->count(),

            'total_repairs' => (clone $baseQuery)
                ->where('action_type', 'repair')
                ->count(),

            'total_cost' => (clone $baseQuery)
                ->sum('part_cost'),

            'under_warranty' => (clone $baseQuery)
                ->where('warranty_expiry_date', '>=', now())
                ->count(),
        ];

        $vehicles = Vehicle::all();
        $parts = VehiclePart::active()->get();

        return view(
            'VehicleManagement.VehicleMaintenance.Reports.parts-history',
            compact(
                'partsHistory',
                'stats',
                'vehicles',
                'parts',
                'vehicleId',
                'partId',
                'startDate',
                'endDate'
            )
        );
    }


    /**
     * Service due report
     */
    public function serviceDue(Request $request)
    {
        $daysAhead = intval($request->input('days_ahead', 30));

        $vehiclesDue = Vehicle::whereHas('maintenances', function ($q) use ($daysAhead) {
                $q->where('current_service_completed', true)
                  ->where(function ($q2) use ($daysAhead) {
                      $q2->where('next_service_due_date', '<=', now()->addDays($daysAhead)->toDateString())
                         ->orWhereRaw('next_service_due_km <= (SELECT meter_reading FROM vehicle_operational_logs WHERE vehicle_id = vehicles.id ORDER BY logged_at DESC LIMIT 1)');
                  });
            })
            ->with(['maintenances' => function ($q) {
                $q->where('current_service_completed', true)
                  ->latest('start_datetime')
                  ->limit(1);
            }])
            ->get()
            ->map(function ($vehicle) {
                $lastMaintenance = $vehicle->maintenances->first();
                
                return [
                    'vehicle' => $vehicle,
                    'last_maintenance' => $lastMaintenance,
                    'days_until_due' => $lastMaintenance ? intval($lastMaintenance->getDaysUntilDue()) : null,
                    'km_until_due' => $lastMaintenance ? $lastMaintenance->getKmUntilDue() : null,
                    'is_overdue' => $lastMaintenance ? $lastMaintenance->isDue() : false,
                ];
            })
            ->sortBy(function ($item) {
                return $item['days_until_due'] ?? 999;
            });

        return view('VehicleManagement.VehicleMaintenance.Reports.service-due', compact('vehiclesDue', 'daysAhead'));
    }

    /**
     * Vendor comparison report
     */
    public function vendorComparison(Request $request)
    {
        $startDate = $request->input('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $vendors = Vendor::with(['maintenances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_datetime', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($vendor) {
                $maintenances = $vendor->maintenances;
                
                return [
                    'vendor' => $vendor,
                    'total_services' => $maintenances->count(),
                    'total_cost' => $maintenances->sum('total_service_cost'),
                    'avg_cost' => $maintenances->avg('total_service_cost'),
                    'routine_services' => $maintenances->where('maintenance_type', 'routine')->count(),
                    'breakdown_services' => $maintenances->where('maintenance_type', 'breakdown')->count(),
                    'vehicles_serviced' => $maintenances->pluck('vehicle_id')->unique()->count(),
                    'avg_rating' => $vendor->rating,
                ];
            })
            ->sortByDesc('total_cost');

        return view('VehicleManagement.VehicleMaintenance.Reports.vendor-comparison', compact('vendors', 'startDate', 'endDate'));
    }

    // =========================================================================
    // VENDOR MONTHLY BILL — PREVIEW
    // =========================================================================

    public function vendorBill(Request $request)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        if (!$request->filled('vendor_id')) {
            return view('VehicleManagement.VehicleMaintenance.Reports.vendor-bill', compact('vendors'));
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'month'     => 'required|integer|min:1|max:12',
            'year'      => 'required|integer|min:2000|max:2100',
        ]);

        $data = $this->buildBillData($request);

        return view('VehicleManagement.VehicleMaintenance.Reports.vendor-bill', array_merge(['vendors' => $vendors], $data));
    }

    // =========================================================================
    // VENDOR MONTHLY BILL — EXCEL EXPORT
    // =========================================================================

    public function vendorBillExport(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'month'     => 'required|integer|min:1|max:12',
            'year'      => 'required|integer|min:2000|max:2100',
        ]);

        $data     = $this->buildBillData($request);
        $filename = 'vendor_bill_'
            . str_replace(' ', '_', $data['vendor']->name)
            . '_' . $data['monthName']
            . '_' . $data['year']
            . '.xlsx';

        $tmpPath = tempnam(sys_get_temp_dir(), 'vendor_bill_') . '.xlsx';

        $this->generateBillExcel($tmpPath, $data);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // =========================================================================
    // PRIVATE — BILL DATA BUILDER
    // =========================================================================

    private function buildBillData(Request $request): array
    {
        $vendor      = Vendor::findOrFail($request->vendor_id);
        $month       = (int) $request->month;
        $year        = (int) $request->year;
        $paymentType = $request->input('payment_type', 'BEFTN');
        $monthName   = date('F', mktime(0, 0, 0, $month, 1));

        $maintenances = VehicleMaintenance::with(['vehicle',  'maintenanceParts.part', 'invoice'])
            ->where('vendor_id', $vendor->id)
            ->whereYear('start_datetime', $year)
            ->whereMonth('start_datetime', $month)
            ->orderBy('start_datetime')
            ->get();

        $rows = collect();
        $sl   = 1;

        foreach ($maintenances as $maintenance) {
            $vehicle = $maintenance->vehicle;
            
            $parts   = $maintenance->maintenanceParts;

            // Parts row
            $particulars = $parts
                ->map(fn($p) => $p->part->part_name ?? '')
                ->filter()
                ->implode(', ');

            if ($particulars) {
                $taka      = (float) $parts->sum('part_cost');
                $vatRate   = 0.15;
                $vatAmount = $taka * $vatRate;
                $rows->push($this->makeBillRow($sl++, $maintenance, $vehicle, $particulars, $taka, $vatRate, $vatAmount));
            }

            // Service charge row
            $labor = (float) $maintenance->labor_cost;
            if ($labor > 0) {
                $vatRate   = 0.10;
                $vatAmount = $labor * $vatRate;
                $rows->push($this->makeBillRow($sl++, $maintenance, $vehicle, 'Service Charge', $labor, $vatRate, $vatAmount));
            }
        }

        $totals = [
            'taka'         => $rows->sum('taka'),
            'vat_amount'   => $rows->sum('vat_amount'),
            'total_amount' => $rows->sum('total_amount'),
        ];

        $amountInWords = $this->numberToWords($totals['total_amount']);

        return compact('rows', 'totals', 'amountInWords', 'vendor', 'monthName', 'year', 'paymentType');
    }

    // =========================================================================
    // PRIVATE — BILL ROW BUILDER
    // =========================================================================

    private function makeBillRow(
        int $sl,
        VehicleMaintenance $maintenance,
        $vehicle,
        string $particulars,
        float $taka,
        float $vatRate,
        float $vatAmount
    ): array {
        return [
            'sl'             => $sl,
            'date'           => $maintenance->start_datetime->format('Y-m-d'),
            'particulars'    => $particulars,
            'vehicle_no'     => $vehicle->registration_number  ?? '',
            'vehicle_type'   => $vehicle->vehicleType->type_name.' '.$vehicle->brand ?? '',
            'reg_year'       => $vehicle->reg_year    ?? '',
            'engine_cc'      => $vehicle->engine_cc            ?? '',
            'vehicle_weight' => $vehicle->vehicle_weight              ?? '',
            'location'       => $vehicle->location             ?? '',
            'present_km'     => $maintenance->meter_reading_at_service ?? 0,
            'previous_km'    => 0,
            'consumption'    => 0,
            'vo_ref_no'      => $maintenance->invoice?->invoice_number ?? '',
            'price'          => '',
            'qty'            => '',
            'taka'           => $taka,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmount,
            'total_amount'   => $taka + $vatAmount,
            'remarks'        => '',
        ];
    }

    // =========================================================================
    // PRIVATE — EXCEL GENERATOR
    // =========================================================================

    private function generateBillExcel(string $outputPath, array $data): void
    {
        $jsonPath = tempnam(sys_get_temp_dir(), 'bill_data_') . '.json';

        file_put_contents($jsonPath, json_encode([
            'output_path'     => $outputPath,
            'vendor_name'     => $data['vendor']->name,
            'month_name'      => $data['monthName'],
            'year'            => $data['year'],
            'payment_type'    => $data['paymentType'],
            'rows'            => $data['rows']->values()->toArray(),
            'totals'          => $data['totals'],
            'amount_in_words' => $data['amountInWords'],
            'report_date'     => now()->format('d.m.Y'),
        ], JSON_UNESCAPED_UNICODE));

        $scriptPath = base_path('scripts/vendor_bill_excel.py');
        $result     = shell_exec("python3 {$scriptPath} " . escapeshellarg($jsonPath) . " 2>&1");

        @unlink($jsonPath);

        if (!file_exists($outputPath)) {
            throw new \RuntimeException('Excel generation failed: ' . $result);
        }
    }

    // =========================================================================
    // PRIVATE — NUMBER TO WORDS  (Crore / Lac / Thousand, Taka)
    // =========================================================================

    private function numberToWords(float $number): string
    {
        $n    = (int) round($number);
        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n === 0)      return '';
            if ($n < 20)       return $ones[$n];
            if ($n < 100)      return $tens[(int) ($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            if ($n < 1000)     return $ones[(int) ($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : '');
            if ($n < 100000)   return $convert((int) ($n / 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
            if ($n < 10000000) return $convert((int) ($n / 100000)) . ' Lac' . ($n % 100000 ? ' ' . $convert($n % 100000) : '');
            return $convert((int) ($n / 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };

        return 'Amount in words: ' . $convert($n) . ' Taka Only.';
    }
}
