<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\ElectricityMeter;
use App\Models\ElectricityMeterNoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ElectricityMeterNocController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(ElectricityMeter $meter)
    {
        $nocs = $meter->nocs()->with('creator')->get()->map(function ($noc) {
            return [
                'id'                => $noc->id,
                'noc_number'        => $noc->noc_number,
                'period_start_date' => $noc->period_start_date->format('Y-m-d'),
                'period_end_date'   => $noc->period_end_date->format('Y-m-d'),
                'period_formatted'  => $noc->period_start_date->format('M d, Y') . ' - ' . $noc->period_end_date->format('M d, Y'),
                'issuing_authority' => $noc->issuing_authority ?: 'N/A',
                'file_url'          => Storage::url($noc->file_path),
                'remarks'           => $noc->remarks ?: '',
                'status_label'      => $noc->status_label,
                'status_badge'      => $noc->status_badge,
                'created_by_name'   => $noc->creator->name ?? 'System',
                'created_at'        => $noc->created_at->format('d-m-Y H:i'),
            ];
        });

        $activeNoc = $meter->getActiveNocForDate();

        return response()->json([
            'meter_id'     => $meter->id,
            'meter_number' => $meter->meter_number,
            'nocs'         => $nocs,
            'has_active'   => $activeNoc ? true : false,
            'active_noc'   => $activeNoc ? [
                'noc_number'       => $activeNoc->noc_number,
                'period_formatted' => $activeNoc->period_start_date->format('M d, Y') . ' - ' . $activeNoc->period_end_date->format('M d, Y'),
            ] : null,
        ]);
    }

    public function store(Request $request, ElectricityMeter $meter)
    {
        $validated = $request->validate([
            'noc_number'        => 'required|string|max:100',
            'period_start_date' => 'required|date',
            'period_end_date'   => 'required|date|after_or_equal:period_start_date',
            'issuing_authority' => 'nullable|string|max:100',
            'noc_file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'           => 'nullable|string',
        ]);

        $filePath = $request->file('noc_file')->store('electricity-meters/nocs', 'public');

        $noc = $meter->nocs()->create([
            'noc_number'        => $validated['noc_number'],
            'period_start_date' => $validated['period_start_date'],
            'period_end_date'   => $validated['period_end_date'],
            'issuing_authority' => $validated['issuing_authority'] ?? null,
            'file_path'          => $filePath,
            'remarks'           => $validated['remarks'] ?? null,
            'created_by'        => Auth::id(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'NOC document uploaded successfully.',
                'noc'     => $noc,
            ]);
        }

        return redirect()->back()->with('success', 'NOC document uploaded successfully.');
    }

    public function destroy(ElectricityMeterNoc $noc)
    {
        $noc->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'NOC document removed successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'NOC document removed successfully.');
    }
}
