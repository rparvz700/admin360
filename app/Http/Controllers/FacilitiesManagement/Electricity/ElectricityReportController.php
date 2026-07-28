<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\ElectricityBill;
use App\Models\PropertiesBuilding;
use App\Models\Rio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectricityReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $rios = Rio::where('is_active', true)->orderBy('name')->get();
        $buildings = PropertiesBuilding::orderBy('site_name')->get();
        
        $selectedRio = $request->get('rio_id', 'all');
        $selectedMonth = $request->get('month', date('Y-m'));

        // 1. Site Costing Summary
        $siteCostingQuery = ElectricityBill::select(
                'building_id',
                DB::raw('COUNT(id) as total_bills'),
                DB::raw('SUM(total_amount) as total_cost'),
                DB::raw('SUM(units_consumed) as total_units')
            )
            ->where('status', '!=', 'cancelled')
            ->groupBy('building_id')
            ->with('building.rio');

        if ($selectedRio !== 'all') {
            $siteCostingQuery->where('rio_id', $selectedRio);
        }

        $siteCostings = $siteCostingQuery->get();

        // 2. RIO Monthly Summary
        $rioSummary = ElectricityBill::select(
                'rio_id',
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(units_consumed) as total_units'),
                DB::raw('COUNT(id) as bill_count')
            )
            ->where('status', '!=', 'cancelled')
            ->groupBy('rio_id')
            ->with('rio')
            ->get();

        // 3. Payment Status Breakdown
        $paymentStatusSummary = ElectricityBill::select(
                'status',
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // 4. Monthly Trend Data (Last 6 Months)
        $monthlyTrendsQuery = ElectricityBill::select(
                'billing_month',
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(units_consumed) as total_units')
            )
            ->where('status', '!=', 'cancelled')
            ->groupBy('billing_month')
            ->orderBy(DB::raw('MAX(id)'), 'desc');

        if ($selectedRio !== 'all') {
            $monthlyTrendsQuery->where('rio_id', $selectedRio);
        }

        $monthlyTrends = $monthlyTrendsQuery->limit(6)->get();

        return view('FacilitiesManagement.Electricity.Reports.index', compact(
            'rios',
            'buildings',
            'siteCostings',
            'rioSummary',
            'paymentStatusSummary',
            'monthlyTrends',
            'selectedRio',
            'selectedMonth'
        ));
    }
}
