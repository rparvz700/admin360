<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\ElectricityBill;
use App\Models\PropertiesBuilding;
use App\Models\Project;
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
        $buildings = PropertiesBuilding::orderBy('site_name')->get();
        $projects  = Project::orderBy('name')->get();

        $selectedBuilding = $request->get('building_id', 'all');
        $selectedProject  = $request->get('project_name', 'all');
        $selectedBillType = $request->get('bill_type', 'all');
        $selectedStatus   = $request->get('status', 'all');

        $query = ElectricityBill::with(['building', 'meter'])
            ->where('status', '!=', 'cancelled');

        if ($selectedBuilding !== 'all') {
            $query->where('building_id', $selectedBuilding);
        }
        if ($selectedProject !== 'all') {
            $query->where('project_name', $selectedProject);
        }
        if ($selectedBillType !== 'all') {
            $query->where('bill_type', $selectedBillType);
        }
        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        // 1. KPI Aggregates
        $totalExpenditure = (clone $query)->sum('total_amount');
        $totalUnits       = (clone $query)->sum('units_consumed');
        $totalBaseAmount  = (clone $query)->sum('net_amount');
        $totalVatAmount   = (clone $query)->sum(DB::raw('vat_amount + late_fee + meter_charge + others_amount'));
        $totalBillsCount  = (clone $query)->count();

        // Payment status counts
        $pendingAmount = ElectricityBill::where('status', 'generated')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->sum('total_amount');
            
        $pendingCount  = ElectricityBill::where('status', 'generated')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->count();

        $paidAmount = ElectricityBill::where('status', 'paid')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->sum('total_amount');

        $paidCount  = ElectricityBill::where('status', 'paid')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->count();

        // 2. Site / Building Costing Breakdown
        $siteCostings = ElectricityBill::select(
                'building_id',
                DB::raw('COUNT(id) as total_bills'),
                DB::raw('SUM(units_consumed) as total_units'),
                DB::raw('SUM(net_amount) as total_net'),
                DB::raw('SUM(vat_amount + late_fee + meter_charge + others_amount) as total_vat'),
                DB::raw('SUM(total_amount) as total_cost')
            )
            ->where('status', '!=', 'cancelled')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->when($selectedStatus !== 'all', fn($q) => $q->where('status', $selectedStatus))
            ->groupBy('building_id')
            ->with('building')
            ->orderBy('total_cost', 'desc')
            ->get();

        // 3. Project-wise Summary Breakdown
        $projectSummary = ElectricityBill::select(
                'project_name',
                DB::raw('COUNT(id) as total_bills'),
                DB::raw('SUM(units_consumed) as total_units'),
                DB::raw('SUM(net_amount) as total_net'),
                DB::raw('SUM(vat_amount + late_fee + meter_charge + others_amount) as total_vat'),
                DB::raw('SUM(total_amount) as total_cost')
            )
            ->where('status', '!=', 'cancelled')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->when($selectedStatus !== 'all', fn($q) => $q->where('status', $selectedStatus))
            ->groupBy('project_name')
            ->orderBy('total_cost', 'desc')
            ->get();

        // 4. Monthly Trend Breakdown
        $monthlyTrends = ElectricityBill::select(
                'billing_month',
                DB::raw('COUNT(id) as total_bills'),
                DB::raw('SUM(units_consumed) as total_units'),
                DB::raw('SUM(net_amount) as total_net'),
                DB::raw('SUM(vat_amount + late_fee + meter_charge + others_amount) as total_vat'),
                DB::raw('SUM(total_amount) as total_cost'),
                DB::raw("SUM(CASE WHEN bill_type = 'postpaid' THEN total_amount ELSE 0 END) as postpaid_cost"),
                DB::raw("SUM(CASE WHEN bill_type = 'prepaid' THEN total_amount ELSE 0 END) as prepaid_cost")
            )
            ->where('status', '!=', 'cancelled')
            ->when($selectedBuilding !== 'all', fn($q) => $q->where('building_id', $selectedBuilding))
            ->when($selectedProject !== 'all', fn($q) => $q->where('project_name', $selectedProject))
            ->when($selectedBillType !== 'all', fn($q) => $q->where('bill_type', $selectedBillType))
            ->when($selectedStatus !== 'all', fn($q) => $q->where('status', $selectedStatus))
            ->groupBy('billing_month')
            ->orderBy(DB::raw('MAX(id)'), 'desc')
            ->limit(12)
            ->get();

        return view('FacilitiesManagement.Electricity.Reports.index', compact(
            'buildings',
            'projects',
            'selectedBuilding',
            'selectedProject',
            'selectedBillType',
            'selectedStatus',
            'totalExpenditure',
            'totalUnits',
            'totalBaseAmount',
            'totalVatAmount',
            'totalBillsCount',
            'pendingAmount',
            'pendingCount',
            'paidAmount',
            'paidCount',
            'siteCostings',
            'projectSummary',
            'monthlyTrends'
        ));
    }
}
