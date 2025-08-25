<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Models\PropertiesBuilding;
use App\Models\PropertiesFloor;
use App\Models\Agreement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $buildingsCount = PropertiesBuilding::count();
        $floorsCount = PropertiesFloor::count();
        // Placeholder for air conditions count
        $airConditionsCount = 0;
        $agreementsExpiring = Agreement::whereMonth('to_date', now()->month)
            ->whereYear('to_date', now()->year)
            ->count();
        // Try 'payment_status' if 'status' does not exist. Adjust as needed.
        $pendingPayments = 0;
        if (\Schema::hasColumn('payments', 'payment_status')) {
            $pendingPayments = Payment::where('payment_status', 'pending')->count();
        }

        return view('FacilitiesManagement.Dashboard.index', compact(
            'buildingsCount',
            'floorsCount',
            'airConditionsCount',
            'agreementsExpiring',
            'pendingPayments'
        ));
    }
}
